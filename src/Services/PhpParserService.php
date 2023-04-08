<?php

namespace LaraJS\Core\Services;

use Exception;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinter\Standard;

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

    public function addItemToArray(string $template, string $arrayParent, string $key, string $value): string
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($template);
        $nodeFinder = new NodeFinder();
        $loginNode = $nodeFinder->findFirst($ast, function (Node $node) use ($arrayParent) {
            return $node instanceof Node\Expr\ArrayItem &&
                $node->key instanceof Node\Scalar\String_ &&
                $node->key->value === $arrayParent;
        });
        if ($loginNode !== null && $loginNode->value instanceof Node\Expr\Array_) {
            $loginNode->value->items[] = new Node\Expr\ArrayItem(
                new Node\Scalar\String_($value),
                new Node\Scalar\String_($key),
            );
        }
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($ast);
    }
}
