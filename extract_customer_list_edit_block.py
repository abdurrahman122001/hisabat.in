from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('[H,W]=S.useState(null),[J,ce]=S.useState({})')
end = s.find('`;A.document.open(),A.document.write(L),A.document.close()', start)
print('start', start)
print('end', end)
if start != -1 and end != -1:
    print(s[start:end])
