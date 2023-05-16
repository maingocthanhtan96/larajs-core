<?php

namespace LaraJS\Core\Services;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\HttpFoundation\Response;

class PhpParserService
{
    public function addStringToArray($template, $field, $property): string
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
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
        $prettyPrinter = new PrettyPrinter\Standard();

        return $prettyPrinter->prettyPrintFile($ast);
    }

    public function addItemToArray(string $template, string $arrayParent, array $items): string
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
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
        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile($ast);
    }

    public function addTemplateToArrayWithReturn(string $template, string $code): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
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
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $node = $nodeFinder->findFirst($ast, function (Node $node) use ($functionName) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $functionName;
        });
        $node->stmts[] = $parser->parse('<?php ' . $code . ' ?>')[0];
        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile($ast);
    }

    public function usePackage(string $template, string $code): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($template);
        $traverser = new NodeTraverser();
        $nodeFinder = new NodeFinder();
        $traverser->addVisitor(new NameResolver());
        $namespace = $nodeFinder->findFirstInstanceOf($stmts, Node\Stmt\Namespace_::class);
        $namespace->stmts = array_merge(
            [new Node\Stmt\Use_([new Node\Stmt\UseUse(new Node\Name($code))])],
            $namespace->stmts,
        );

        $printer = new Standard();

        return $printer->prettyPrintFile($stmts);
    }

    public function addFakerToFactory(string $template, array $fields, $isSignature = false): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($template);
        $finder = new NodeFinder();
        $methodNode = $finder->findFirstInstanceOf($ast, Node\Stmt\ClassMethod::class);
        $returnNode = $finder->findFirstInstanceOf($methodNode, Node\Stmt\Return_::class);
        $returnNode->expr->items = array_merge($returnNode->expr->items, $this->itemFakers($fields));
        if ($isSignature) {
            $userFactory = new Expr\StaticCall(new Node\Name('\App\Models\User'), new Node\Identifier('factory'));
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
        $printer = new PrettyPrinter\Standard();

        return $printer->prettyPrintFile($ast);
    }

    public function addNewMethod(string $template, string $methodName, $argNumber = 0): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $methodCall = $nodeFinder->findFirstInstanceOf($ast, Node\Expr\MethodCall::class);
        $methodCall->var = new Node\Expr\MethodCall($methodCall->var, $methodName);
        if ($argNumber) {
            $methodCall->var->args[] = new Node\Arg(new Node\Scalar\LNumber($argNumber));
        }
        $printer = new Standard();

        return $printer->prettyPrintFile($ast);
    }

    public function runParserJS($file, $data, $templateDataReal = null): string
    {
        if ($templateDataReal) {
            if (!file_exists(dirname($file))) {
                mkdir(dirname($file), 0755, true);
            }
            file_put_contents($file, $templateDataReal);
        }
        $node = __DIR__ . '/../server-parser.js';
        $cmd = "node '$node' '$file' " . "'" . json_encode($data) . "'";
        exec($cmd, $output);
        abort_if(!$output, Response::HTTP_FORBIDDEN, 'Node parser output empty!');

        return implode(PHP_EOL, $output);
    }

    public function itemFakers($fields): array
    {
        $dbType = config('generator.db_type');
        $data = [];
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $faker = match ($field['db_type']) {
                    $dbType['integer'], $dbType['bigInteger'] => [
                        'faker' => 'numberBetween',
                        'args' => [],
                    ],
                    $dbType['float'], $dbType['double'] => [
                        'faker' => 'randomFloat',
                        'args' => [
                        ],
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
                        'args' => [
                        ],
                    ],
                    $dbType['time'] => [
                        'faker' => 'time',
                        'args' => [
                        ],
                    ],
                    $dbType['year'] => [
                        'faker' => 'year',
                        'args' => [
                        ],
                    ],
                    $dbType['string'] => [
                        'faker' => 'name',
                        'args' => [
                        ],
                    ],
                    $dbType['text'], $dbType['longtext'] => [
                        'faker' => 'text',
                        'args' => [
                        ],
                    ],
                    $dbType['enum'] => [
                        'faker' => 'randomElement',
                        'enum' => $field['enum'],
                        'args' => [
                        ],
                    ],
                    $dbType['json'] => [],
                };
                $faker['key'] = $field['field_name'];
                $faker['db_type'] = $field['db_type'];
                $data[] = $faker;
            }
        }
        $itemFakers = [];
        foreach ($data as $item) {
            switch ($item['db_type']) {
                case $dbType['enum']:
                    $enum = \Arr::map($item['enum'], function ($value) {
                        if (is_numeric($value)) {
                            return new Node\Scalar\LNumber($value);
                        } else {
                            return new Node\Scalar\String_($value);
                        }
                    });
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
                default:
                    $itemFakers[] = new Node\Expr\ArrayItem(
                        new Node\Expr\MethodCall(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), 'faker'), $item['faker'], $item['args']),
                        new Node\Scalar\String_($item['key']),
                    );
            }
        }

        return $itemFakers;
    }
}
