from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find(',e==="materials"?o.jsxs(o.Fragment,{children:[')
end = s.find('function Lk(){')
frag = s[start:end]
print('len', len(frag))
for label, a, b in [('paren','(',')'),('brace','{','}'),('bracket','[',']')]:
    bal = 0
    for i,ch in enumerate(frag):
        if ch == a:
            bal += 1
        elif ch == b:
            bal -= 1
            if bal < 0:
                print(label, 'first extra close at', i)
                break
    print(label, 'final', bal)
print('TAIL')
print(frag[-220:])
