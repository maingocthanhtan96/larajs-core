import { readFileSync } from 'node:fs';
import * as babelParser from '@babel/parser';
import _traverse from '@babel/traverse';
import t from '@babel/types';
import _generate from '@babel/generator';
import prettier from 'prettier';
import { parse } from '@vue/compiler-sfc';

let ast;
const traverse = _traverse.default;
const generate = _generate.default;
const astParser = code =>
  babelParser.parse(code, {
    sourceType: 'module',
    plugins: ['jsx', 'typescript'],
  });
const parserExpression = code =>
  babelParser.parseExpression(code, {
    sourceType: 'module',
    plugins: ['jsx', 'typescript'],
  });
const addImport = (hasImportExist, data, lastImport, ast, isImportDefault = false) => {
  if (!hasImportExist && data.name && data.path) {
    const newImport = t.importDeclaration(
      [
        t[isImportDefault ? 'importDefaultSpecifier' : 'importSpecifier'](
          t.identifier(data.name),
          t.identifier(data.name)
        ),
      ],
      t.stringLiteral(data.path)
    );
    if (lastImport) {
      lastImport.insertAfter(t.jsxText('\n'));
      lastImport.insertAfter(newImport);
    } else {
      ast.program.body.unshift(t.jsxText('\n'));
      ast.program.body.unshift(newImport);
    }
  }
};
// Trim specific characters from the beginning and end of a string
const trimCharacters = (inputString, charactersToTrim) => {
  // Create a regular expression pattern to match the specified characters
  const pattern = `^[${charactersToTrim}]+|[${charactersToTrim}]+$`;
  // eslint-disable-next-line security/detect-non-literal-regexp
  const regex = new RegExp(pattern, 'g');
  // Use replace() to remove the matched characters
  return inputString.replace(regex, '');
};

try {
  const codeContent = readFileSync(process.argv[2], 'utf8');
  const data = JSON.parse(atob(process.argv[3]));
  switch (data.key) {
    case 'views.form:import':
    case 'views.form:create':
    case 'views.form:edit': {
      const { descriptor } = parse(codeContent);
      const scriptSetupBlock = descriptor.scriptSetup;
      const scriptContent = scriptSetupBlock.content;
      ast = astParser(scriptContent);
      break;
    }
    default: {
      ast = astParser(codeContent);
    }
  }
  let lastImport = null;
  let hasImportExist = null;
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
          const importSpecifiers = path.node.specifiers;
          hasImportExist = importSpecifiers.some(specifier => specifier.local.name === data.name);
        },
      });
      addImport(hasImportExist, data, lastImport, ast, true);
      break;
    }
    case 'common.import': {
      traverse(ast, {
        ImportDeclaration(path) {
          lastImport = path;
          const importSpecifiers = path.node.specifiers;
          hasImportExist = importSpecifiers.some(specifier => specifier.local.name === data.name);
        },
        TSInterfaceDeclaration(path) {
          if (path.node.id.name === data.interface) {
            Object.keys(data.items).forEach(field => {
              const item = t.objectTypeProperty(
                t.identifier(field),
                t.genericTypeAnnotation(t.identifier(data.items[field]))
              );
              path.node.body.body?.push(item);
            });
          }
        },
      });
      addImport(hasImportExist, data, lastImport, ast);
      break;
    }
    case 'uses.index': {
      traverse(ast, {
        FunctionDeclaration(path) {
          if (path.node.id.name === data.name) {
            const returnStatement = path
              .get('body')
              .get('body')
              .find(node => node.isReturnStatement());
            if (returnStatement) {
              const returnObject = returnStatement.get('argument');
              returnStatement.insertBefore(astParser(data.value));
              returnObject.node.properties?.push(babelParser.parse(data.property).program.body[0].expression);
            }
          }
        },
      });
      break;
    }
    case 'uses.form': {
      traverse(ast, {
        ImportDeclaration(path) {
          lastImport = path;
          const importSpecifiers = path.node.specifiers;
          hasImportExist = importSpecifiers.some(specifier => specifier.local.name === data.name);
        },
        TSInterfaceDeclaration(path) {
          if (path.node.id.name === data.interface) {
            Object.keys(data.items).forEach(field => {
              const item = t.objectTypeProperty(
                t.identifier(field),
                t.genericTypeAnnotation(t.identifier(data.items[field]))
              );
              path.node.body.body?.push(item);
            });
          }
        },
      });
      addImport(hasImportExist, data, lastImport, ast);
      break;
    }
    case 'uses.form:item': {
      traverse(ast, {
        VariableDeclarator(path) {
          if (t.isIdentifier(path.node.id) && path.node.id.name === data.variable) {
            const generateValue = item => {
              switch (item.type) {
                case 'number': {
                  return t.numericLiteral(+item.value);
                }
                case 'string': {
                  return t.stringLiteral(item.value);
                }
                case 'boolean': {
                  return t.booleanLiteral(item.value);
                }
                case 'array': {
                  const array = t.arrayExpression([]);
                  (item.value || []).forEach(value => {
                    array.elements?.push(isNaN(value) ? t.stringLiteral(value) : t.numericLiteral(+value)); // eslint-disable-line
                  });
                  return array;
                }
                case 'null': {
                  return t.nullLiteral();
                }
                case 'json': {
                  return t.objectExpression([]);
                }
                case 'CURRENT_TIMESTAMPS': {
                  return t.callExpression(t.identifier('parseTime'), [t.newExpression(t.identifier('Date'), [])]);
                }
              }
            };
            Object.keys(data.items).forEach(field => {
              const item = t.objectProperty(t.identifier(field), generateValue(data.items[field]));
              path.node.init?.arguments[0]?.properties?.push(item);
            });
          }
        },
      });
      break;
    }
    case 'uses.form:rules': {
      traverse(ast, {
        FunctionDeclaration(path) {
          if (t.isIdentifier(path.node.id) && path.node.id.name === data.variable) {
            path.traverse({
              ReturnStatement(path) {
                Object.keys(data.items).forEach(field => {
                  const properties = path.node.argument.properties;
                  properties?.push(t.objectProperty(t.identifier(field), parserExpression(data.items[field])));
                });
              },
            });
          }
        },
      });
      break;
    }
    case 'uses.form:items': {
      traverse(ast, {
        VariableDeclarator(path) {
          if (t.isIdentifier(path.node.id, { name: 'formElement' })) {
            const astItems = path.node.init.properties?.find(property =>
              t.isIdentifier(property.key, { name: 'items' })
            );
            if (astItems?.value?.elements) {
              (data.items || []).forEach(item => {
                astItems.value.elements.push(parserExpression(item));
              });
            }
          }
        },
      });
      break;
    }
    case 'uses.table:search:column':
    case 'uses.table:date:column':
    case 'uses.table:include':
    case 'uses.table:columns': {
      traverse(ast, {
        VariableDeclarator(path) {
          if (t.isIdentifier(path.node.id, { name: 'table' })) {
            let astItems;
            const key = data.key.split(':');
            const name = key[1];
            if (['columns'].includes(name)) {
              astItems = path.node.init.properties?.find(property => t.isIdentifier(property.key, { name: name }));
            } else {
              const queryAstItems = path.node.init.properties?.find(property =>
                t.isIdentifier(property.key, { name: 'query' })
              );
              const findAstItem = () => {
                return queryAstItems?.value?.properties?.find(property => t.isIdentifier(property.key, { name: name }));
              };
              astItems = findAstItem();
              if (!astItems) {
                queryAstItems.value.properties?.push(
                  t.objectProperty(
                    t.identifier(name),
                    t.objectExpression([t.objectProperty(t.identifier('column'), t.stringLiteral(''))])
                  )
                );
                astItems = findAstItem();
              }
              if (key.length === 3) {
                astItems = astItems?.value?.properties?.find(property =>
                  t.isIdentifier(property.key, { name: key[2] })
                );
              }
            }

            if (astItems?.value?.elements) {
              (data.items || []).forEach(item => {
                switch (name) {
                  case 'include': {
                    astItems.value.elements.push(t.stringLiteral(item));
                    break;
                  }
                  case 'columns': {
                    astItems.value.elements.push(parserExpression(item));
                  }
                }
              });
            } else if (astItems?.value?.value !== undefined) {
              astItems.value.value = trimCharacters(
                trimCharacters(astItems.value.value, ',') + ',' + data.items.join(','),
                ','
              );
            }
          }
        },
      });
      break;
    }
    case 'views.form:import': {
      let hasImportUses = false;
      traverse(ast, {
        ImportDeclaration(path) {
          lastImport = path;
          const importSpecifiers = path.node.specifiers;
          hasImportExist = importSpecifiers.some(specifier => specifier.local.name === data.name);
        },
        VariableDeclaration(path) {
          if (path.node.declarations.length === 1) {
            const declaration = path.node.declarations[0];
            if (
              declaration.id.type === 'ObjectPattern' &&
              declaration.init &&
              declaration.init.type === 'CallExpression' &&
              declaration.init.callee.name === data.useName &&
              declaration.id.properties.some(
                property => t.isIdentifier(property.key) && property.key.name === data.useKey
              )
            ) {
              hasImportUses = true;
              path.stop();
            }
          }
        },
      });
      if (lastImport && !hasImportUses) {
        const newImport = t.variableDeclaration('const', [
          t.variableDeclarator(
            t.objectPattern([t.objectProperty(t.identifier(data.useKey), t.identifier(data.useKey), false, true)]),
            t.callExpression(t.identifier(data.useName), [])
          ),
        ]);
        lastImport.insertAfter(newImport);
      }
      addImport(hasImportExist, data, lastImport, ast);
      break;
    }
    case 'views.form:create': {
      traverse(ast, {
        ExpressionStatement(path) {
          const { node } = path;
          if (
            t.isCallExpression(node.expression) &&
            t.isMemberExpression(node.expression.callee) &&
            t.isIdentifier(node.expression.callee.object) &&
            ['appStore', 'coreStore'].includes(node.expression.callee.object.name) &&
            t.isIdentifier(node.expression.callee.property, { name: 'setLoading' }) &&
            node.expression.arguments.length === 1 &&
            t.isBooleanLiteral(node.expression.arguments[0], { value: true })
          ) {
            const code = astParser(data.content);
            path.insertAfter(code.program.body);
            path.stop();
          }
        },
      });
      break;
    }
    case 'views.form:edit': {
      const hasIdIdentifierInCondition = node => {
        if (t.isIdentifier(node, { name: 'id' })) {
          return true;
        } else if (node.type === 'LogicalExpression') {
          return hasIdIdentifierInCondition(node.left) || hasIdIdentifierInCondition(node.right);
        }
        return false;
      };
      traverse(ast, {
        CallExpression(path) {
          if (t.isIdentifier(path.node.callee, { name: 'show' })) {
            let paramsNode = path.node.arguments[1]?.properties?.find(prop =>
              t.isIdentifier(prop.key, { name: 'params' })
            );
            if (!paramsNode) {
              paramsNode = t.objectProperty(t.identifier('params'), t.objectExpression([]));
              path.node.arguments[1].properties?.push(paramsNode);
            }
            let includeNode = paramsNode.value.properties?.find(prop => {
              return t.isIdentifier(prop.key, { name: 'include' });
            });
            if (includeNode) {
              includeNode.value.elements?.push(t.stringLiteral(data.relationFunction));
            } else {
              includeNode = t.objectProperty(
                t.identifier('include'),
                t.arrayExpression([t.stringLiteral(data.relationFunction)])
              );
              paramsNode.value.properties?.push(includeNode);
            }
          }
        },
        IfStatement(path) {
          const { node } = path;
          if (hasIdIdentifierInCondition(node.test)) {
            const code = astParser(data.content);
            node.consequent.body?.push(...code.program.body);
          }
        },
      });
      break;
    }
  }
  const { code } = generate(ast);
  let formattedCode = await prettier.format(code, {
    parser: 'typescript',
    semi: true,
    singleQuote: true,
    arrowParens: 'avoid',
    htmlWhitespaceSensitivity: 'ignore',
    jsxSingleQuote: true,
    printWidth: 120,
    proseWrap: 'always',
  });

  switch (data.key) {
    case 'views.form:import':
    case 'views.form:create':
    case 'views.form:edit': {
      const { descriptor } = parse(codeContent);
      const attrs = Object.entries(descriptor.scriptSetup.attrs)
        .map(([key, value]) => (value === true ? key : `${key}="${value}"`))
        .join(' ');
      formattedCode = `<script ${attrs}>\n${formattedCode}</script>\n\n<template>${descriptor.template.content}</template>`;
      break;
    }
  }

  console.log(formattedCode); // eslint-disable-line no-console
} catch (error) {
  // console.log(error); // eslint-disable-line no-console
}
