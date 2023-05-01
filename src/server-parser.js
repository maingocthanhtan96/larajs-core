import { readFileSync } from "node:fs";
import * as babelParser from "@babel/parser";
import _traverse from "@babel/traverse";
import t from "@babel/types";
import _generate from "@babel/generator";
import prettier from "prettier";

const traverse = _traverse.default;
const generate = _generate.default;
const astParser = (code) =>
  babelParser.parse(code, {
    sourceType: "module",
    plugins: ["jsx", "typescript"],
  });

try {
  const codeContent = readFileSync(process.argv[2], "utf8");
  const data = JSON.parse(process.argv[3]);
  const ast = astParser(codeContent);
  let lastImport = null;
  let hasImportExist = null;
  traverse(ast, {
    ObjectProperty(path) {
      const node = path.node;
      switch (data.key) {
        case "query.column_search":
        case "query.relationship": {
          if (node.key.name === "query") {
            node.value.properties.forEach((queryProp) => {
              if (
                t.isIdentifier(queryProp.key) &&
                queryProp.key.name === data.key.split(".")[1]
              ) {
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
    case "router.import": {
      traverse(ast, {
        ArrayExpression(path) {
          if (path.parent?.id?.name === "asyncRouterMap") {
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
      } else {
        ast.program.body.unshift(newImport);
      }
      break;
    }
    case "common.import": {
      traverse(ast, {
        ImportDeclaration(path) {
          lastImport = path;
          const importSpecifiers = path.node.specifiers;
          hasImportExist = importSpecifiers.some(
            (specifier) => specifier.local.name === data.name
          );
        },
        TSInterfaceDeclaration(path) {
          if (path.node.id.name === data.interface) {
            Object.keys(data.items).forEach((field) => {
              const newProperty = t.objectTypeProperty(
                t.identifier(field),
                t.genericTypeAnnotation(t.identifier(data.items[field]))
              );
              path.node.body.body.push(newProperty);
            });
          }
        },
      });
      if (!hasImportExist && data.name && data.path) {
        const newImport = t.importDeclaration(
          [t.importSpecifier(t.identifier(data.name), t.identifier(data.name))],
          t.stringLiteral(data.path)
        );
        if (lastImport) {
          lastImport.insertAfter(newImport);
        } else {
          ast.program.body.unshift(newImport);
        }
      }

      break;
    }
    case "uses.import": {
      traverse(ast, {
        FunctionDeclaration(path) {
          if (path.node.id.name === data.name) {
            const returnStatement = path
              .get("body")
              .get("body")
              .find((node) => node.isReturnStatement());
            if (returnStatement) {
              const returnObject = returnStatement.get("argument");
              returnStatement.insertBefore(astParser(data.value));
              returnObject.node.properties.push(
                babelParser.parse(data.property).program.body[0].expression
              );
            }
          }
        },
      });
      break;
    }
  }
  const { code } = generate(ast);
  const formattedCode = prettier.format(code, {
    parser: "babel",
    singleQuote: true,
    arrowParens: "avoid",
    htmlWhitespaceSensitivity: "ignore",
    jsxSingleQuote: true,
    printWidth: 120,
    proseWrap: "always",
  });

  console.log(formattedCode); // eslint-disable-line no-console
} catch (error) {
  // console.log(error);
}
