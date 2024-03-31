<?php

namespace LaraJS\Core\Services;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PhpParserService
{
    public function addAttribute(string $template, string $attribute, string $className): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $classNode = $nodeFinder->findFirstInstanceOf($ast, Class_::class);
        $classNode->attrGroups[] = new Node\AttributeGroup([
            new Node\Attribute(new Node\Name($attribute), [
                new Node\Expr\Array_([
                    new Node\Expr\ClassConstFetch(new Node\Name($className), new Node\Identifier('class')),
                ]),
            ]),
        ]);

        return $this->prettyPrintFile($ast);
    }

    public function addStringToArray($template, $field, $property): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $fillableNode = $nodeFinder->findFirst($ast, function (Node $node) use ($property) {
            return $node instanceof Node\Stmt\Property && $node->props[0]->name->name === $property;
        });
        if ($fillableNode !== null) {
            $fillableProperty = $fillableNode->props[0];
            if ($fillableProperty->default instanceof Node\Expr\Array_) {
                $fillableProperty->default->items[] = new Node\Scalar\String_($field);
            } else {
                $fillableProperty->default = new Node\Expr\Array_([new Node\Scalar\String_($field)]);
            }
        }

        return $this->prettyPrintFile($ast);
    }

    public function addItemToArray(string $template, string $arrayParent, array $items): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $node = $nodeFinder->findFirst($ast, function (Node $node) use ($arrayParent) {
            return $node instanceof Node\Expr\ArrayItem &&
                $node->key instanceof Node\Scalar\String_ &&
                $node->key->value === $arrayParent;
        });
        if ($node !== null && $node->value instanceof Node\Expr\Array_) {
            foreach ($items as $key => $value) {
                $node->value->items[] = new Node\Expr\ArrayItem(
                    new Node\Scalar\String_($value),
                    new Node\Scalar\String_($key),
                );
            }
        }

        return $this->prettyPrintFile($ast);
    }

    public function addTemplateToArrayWithReturn(string $template, string $code): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $returnStmt = $nodeFinder->findFirstInstanceOf($ast, Return_::class);
        $arrayExpr = $returnStmt->expr;
        $arrayExpr->items[] = $parser->parse($code)[0];
        $prettyPrinter = new Standard();
        $content = $prettyPrinter->prettyPrintFile($ast);
        $content = str_replace(['<?php ,', '<?php', '?>'], '', $content);

        return '<?php' . $content;
    }

    public function addCodeToFunction(string $template, string $code, string $functionName): string
    {
        if (strpos($template, $code)) {
            return $template;
        }
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $node = $nodeFinder->findFirst($ast, function (Node $node) use ($functionName) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $functionName;
        });
        $node->stmts[] = $parser->parse('<?php ' . $code . ' ?>')[0];

        return $this->prettyPrintFile($ast);
    }

    public function usePackage(string $template, string $code, bool $isNamespace = true): string
    {
        $parser = $this->createParser();
        $stmts = $parser->parse($template);
        $traverser = new NodeTraverser();
        $nodeFinder = new NodeFinder();
        $traverser->addVisitor(new NameResolver());
        if ($isNamespace) {
            $namespace = $nodeFinder->findFirstInstanceOf($stmts, Node\Stmt\Namespace_::class);
            $existingUses = $nodeFinder->findInstanceOf($namespace->stmts, Node\Stmt\Use_::class);
        } else {
            $existingUses = $nodeFinder->findInstanceOf($stmts, Node\Stmt\Use_::class);
        }

        $isNotImported = true;
        foreach ($existingUses as $useStatement) {
            foreach ($useStatement->uses as $use) {
                if ($use->name->toString() === $code) {
                    $isNotImported = false;
                    break 2;
                }
            }
        }
        if ($isNotImported) {
            if ($isNamespace) {
                $namespace->stmts = array_merge(
                    [new Node\Stmt\Use_([new Node\Stmt\UseUse(new Node\Name($code))])],
                    $namespace->stmts,
                );
            } else {
                $stmts = array_merge(
                    [new Node\Stmt\Use_([new Node\Stmt\UseUse(new Node\Name($code))])],
                    $stmts,
                );
            }
        }

        return $this->prettyPrintFile($stmts);
    }

    public function addFakerToFactory(string $template, array $fields, $isSignature = false): string
    {
        if (!$fields) {
            return $template;
        }

        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $finder = new NodeFinder();
        $methodNode = $finder->findFirstInstanceOf($ast, Node\Stmt\ClassMethod::class);
        $returnNode = $finder->findFirstInstanceOf($methodNode, Node\Stmt\Return_::class);
        $returnNode->expr->items = array_merge($returnNode->expr->items, $this->itemFakers($fields));
        if ($isSignature) {
            $importLaravel = config('generator.import.laravel.use');
            $userFactory = new Expr\StaticCall(new Node\Name(explode('::', $importLaravel['trait_user_signature']['model'])[0]), new Node\Identifier('factory'));
            $returnNode->expr->items = array_merge($returnNode->expr->items, [
                new Node\Expr\ArrayItem(
                    $userFactory,
                    new Node\Scalar\String_('created_by'),
                ),
                new Node\Expr\ArrayItem(
                    $userFactory,
                    new Node\Scalar\String_('updated_by'),
                ),
            ]);
        }

        return $this->prettyPrintFile($ast);
    }

    public function addNewMethod(string $template, string $methodName, $argNumber = 0): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $methodCall = $nodeFinder->findFirstInstanceOf($ast, Node\Expr\MethodCall::class);
        $methodCall->var = new Node\Expr\MethodCall($methodCall->var, $methodName);
        if ($argNumber) {
            $methodCall->var->args[] = new Node\Arg(new Node\Scalar\LNumber($argNumber));
        }

        return $this->prettyPrintFile($ast);
    }

    public function addItemForAttribute(string $template, string $item, string $identify): string
    {
        $parser = $this->createParser();
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $attributeNodes = $nodeFinder->find($ast, function (Node $node): bool {
            return $node instanceof Node\Attribute && $node->name->toCodeString() === 'ResponseFromApiResource';
        });
        foreach ($attributeNodes as $attributeNode) {
            foreach ($attributeNode->args as $arg) {
                // Find the 'with' argument in the attribute's arguments
                if ($arg->name && $arg->name->name === $identify) {
                    //Add new item to the array
                    $arg->value->items[] = new Node\Scalar\String_($item);
                    break;
                }
            }
        }

        return $this->prettyPrintFile($ast);
    }

    public function runParserJS($file, $data, $templateDataReal = null): string
    {
        if ($templateDataReal) {
            if (!file_exists(dirname($file)) && !mkdir($concurrentDirectory = dirname($file), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            file_put_contents($file, $templateDataReal);
        }
        $node = __DIR__ . '/../server-parser.js';
        $cmd = [config('generator.node_path'), $node, $file, base64_encode(json_encode($data))];
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();
        if (!$output) {
            \Log::error(implode(' ', $cmd), $data);
            abort(Response::HTTP_FORBIDDEN, 'Node parser output empty!');
        }

        return $output;
    }

    public function itemFakers($fields): array
    {
        $dbType = config('generator.db_type');
        $data = [];
        foreach ($fields as $field) {
            if ($field['field_name'] !== 'id') {
                $faker = match ($field['db_type']) {
                    $dbType['integer'], $dbType['bigInteger'] => [
                        'faker' => 'numberBetween',
                        'args' => [],
                    ],
                    $dbType['float'] => [
                        'faker' => 'randomFloat',
                        'args' => [
                            new Node\Scalar\LNumber(2),
                            new Node\Scalar\LNumber(1),
                            new Node\Scalar\LNumber(1000),
                        ],
                    ],
                    $dbType['double'] => [
                        'faker' => 'randomFloat',
                        'args' => [],
                    ],
                    $dbType['boolean'] => [
                        'faker' => 'boolean',
                        'args' => [],
                    ],
                    $dbType['date'] => [
                        'faker' => 'date',
                        'args' => [],
                    ],
                    $dbType['dateTime'], $dbType['timestamp'] => [
                        'faker' => 'dateTime',
                        'args' => [],
                    ],
                    $dbType['time'] => [
                        'faker' => 'time',
                        'args' => [],
                    ],
                    $dbType['year'] => [
                        'faker' => 'year',
                        'args' => [],
                    ],
                    $dbType['string'] => [
                        'faker' => 'name',
                        'args' => [],
                    ],
                    $dbType['text'], $dbType['longtext'] => [
                        'faker' => 'text',
                        'args' => [],
                    ],
                    $dbType['enum'] => [
                        'faker' => 'randomElement',
                        'enum' => $field['enum'],
                        'args' => [],
                    ],
                    $dbType['json'], 'FOREIGN_KEY' => [],
                };
                if ($field['db_type'] === 'FOREIGN_KEY') {
                    $faker['model'] = $field['model'];
                }
                $faker['key'] = $field['field_name'];
                $faker['db_type'] = $field['db_type'];
                $data[] = $faker;
            }
        }
        $itemFakers = [];
        foreach ($data as $item) {
            switch ($item['db_type']) {
                case $dbType['enum']:
                    $enum = \Arr::map($item['enum'], fn ($value) => is_numeric($value) ? new Node\Scalar\LNumber($value) : new Node\Scalar\String_($value));
                    $item['args'] = new Node\Expr\Array_($enum);
                    $itemFakers[] = new Node\Expr\ArrayItem(
                        new Node\Expr\MethodCall(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), 'faker'), $item['faker'], [$item['args']]),
                        new Node\Scalar\String_($item['key']),
                    );
                    break;
                case $dbType['json']:
                    $itemFakers[] = new Node\Expr\ArrayItem(
                        new Node\Scalar\String_('{}'),
                        new Node\Scalar\String_($item['key']),
                    );
                    break;
                case 'FOREIGN_KEY':
                    $itemFakers[] = new Node\Expr\ArrayItem(
                        new Node\Expr\StaticCall(
                            new Node\Name\FullyQualified('App\Models\\' . $item['model']),
                            'factory'
                        ),
                        new Node\Scalar\String_($item['key']),
                    );
                    break;
                default:
                    $itemFakers[] = new Node\Expr\ArrayItem(
                        new Node\Expr\MethodCall(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), 'faker'), $item['faker'], $item['args']),
                        new Node\Scalar\String_($item['key']),
                    );
            }
        }

        return $itemFakers;
    }

    private function prettyPrintFile($ast): string
    {
        return (new Standard())->prettyPrintFile($ast);
    }

    private function createParser(): Parser
    {
        return (new ParserFactory())->createForHostVersion();
    }
}
