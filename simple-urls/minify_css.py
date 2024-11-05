import csscompressor
import os

css_files = [
    'admin/assets/css/lasso-live.min.css',  # Lasso Pro
    'admin/assets/css/lasso-lite.css',      # Lasso Lite
    'admin/assets/css/lasso-table-frontend.min.css',
    'admin/assets/css/lasso-amp.css'
]

for css_file in css_files:
    if os.path.exists(css_file):
        with open(css_file, 'r') as file:
            css_content = file.read()

        minified_css = csscompressor.compress(css_content)

        with open(css_file, 'w') as file:
            file.write(minified_css)
    else:
        print(f"File not found: {css_file}")

print("CSS files have been minified.")
