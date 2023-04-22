import { readFileSync } from 'node:fs';
import * as babelParser from '@babel/parser';
import _traverse from '@babel/traverse';
import t from '@babel/types';
import _generate from '@babel/generator';
import prettier from 'prettier';

const traverse = _traverse.default;
const generate = _generate.default;

try {
  const tsxCode = readFileSync(process.argv[2], 'utf8');
  const data = JSON.parse(process.argv[3]);
  const ast = babelParser.parse(tsxCode, {
    sourceType: 'module',
    plugins: ['jsx', 'typescript'],
  });
  let lastImport = null;
  traverse(ast, {
    ObjectProperty(path) {
      const node = path.node;
      // console.log(node);
      switch (data.key) {
        case 'query.column_search':
        case 'query.relationship': {
          if (node.key.name === 'query') {
            node.value.properties.forEach(queryProp => {
              if (t.isIdentifier(queryProp.key) && queryProp.key.name === data.key.split('.')[1]) {
                queryProp.value.elements.push(t.stringLiteral(data.value));
              }
            });
          }
          break;
        }
      }
    },
  });
  switch (data.key) {
    case 'router.import': {
      traverse(ast, {
        ArrayExpression(path) {
          if (path.parent?.id?.name === 'asyncRouterMap') {
            path.parent.init.elements.unshift(t.identifier(data.name));
          }
        },
        ImportDeclaration(path) {
          lastImport = path;
        },
      });
      const newImport = t.importDeclaration(
        [t.importDefaultSpecifier(t.identifier(data.name))],
        t.stringLiteral(data.path)
      );
      if (lastImport) {
        lastImport.insertAfter(newImport);
      }
      break;
    }
  }
  const { code } = generate(ast);
  const formattedCode = prettier.format(code, {
    parser: 'babel',
    singleQuote: true,
    arrowParens: 'avoid',
    htmlWhitespaceSensitivity: 'ignore',
    jsxSingleQuote: true,
    printWidth: 120,
    proseWrap: 'always',
  });

  console.log(formattedCode); // eslint-disable-line no-console
} catch (error) {
  console.log(error);
}
