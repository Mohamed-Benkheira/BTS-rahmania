# figures/ directory

Place all report images here. The LaTeX report references images with paths like
`figures/image-name.png`.

## Recommended image naming convention

| Type | Filename pattern | Example |
|------|-----------------|---------|
| Architecture diagrams | `arch-*.png` | `arch-web.png`, `arch-tier3.png` |
| Org charts | `org-*.png` | `org-chart.png` |
| UML use case diagrams | `use-case-*.png` | `use-case-overview.png` |
| UML sequence diagrams | `seq-*.png` | `seq-auth.png`, `seq-add-employee.png` |
| UML activity diagrams | `act-*.png` | `act-auth.png` |
| UML class diagrams | `class-diagram.png` | `class-diagram.png` |
| UML deployment diagrams | `deployment.png` | `deployment.png` |
| Tech logos | `logo-*.png` | `logo-html.png`, `logo-laravel.png`, `logo-flutter.png` |
| Interface screenshots | `ui-*.png` | `ui-dashboard.png`, `ui-login.png` |
| Test results | `test-*.png` | `test-postman.png`, `test-phpunit.png` |

## Placeholder behavior

If an image file is missing, use the `\figureplaceholder` command in LaTeX.
It renders a dashed gray box with the caption instead of failing to compile.

## PNG vs PDF/JPG

- Use **PNG** for screenshots and logos (transparency support)
- Use **PDF/vector** for diagrams if possible (crisper rendering)
- pdflatex supports: .png, .jpg, .pdf

## How the report references images

```latex
% With an actual image:
\reportfigure[0.8]{figures/ui-dashboard.png}{Admin dashboard interface}

% Without an image (placeholder):
\figureplaceholder[0.8]{UML Use Case Diagram}
```