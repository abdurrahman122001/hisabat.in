from pathlib import Path

src = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = src.find('e==="materials"?o.jsxs(o.Fragment')
end = src.find('}):null]})}function Lk()', start)
if start == -1 or end == -1:
    raise SystemExit('block not found')
block = src[start:end]
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\materials_block_full.txt').write_text(block, encoding='utf-8')
print('ok', start, end, len(block))
