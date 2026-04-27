from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('const ne=S.useMemo(()=>({konica:new Set([')
end = s.find('m=async h=>{h.preventDefault()', start)
print(s[start:end])
