from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('/hesabat/api/printers_create.php')
end = s.find('e==="materials"')
if start == -1:
    start = s.find('/hesabat/api/materials_update.php')
if end == -1:
    end = start + 40000
chunk = s[max(0,start-1500):min(len(s),end+30000)]
pretty = chunk.replace('}),o.jsx(','}),\n\no.jsx(').replace('}),o.jsxs(','}),\n\no.jsxs(').replace(']),o.jsx(',']),\n\no.jsx(').replace(']),o.jsxs(',']),\n\no.jsxs(').replace('):o.jsx(','):\n\no.jsx(').replace('):o.jsxs(','):\n\no.jsxs(')
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\settings_pretty.txt').write_text(pretty, encoding='utf-8')
print('done', start, end)
