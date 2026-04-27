from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
stack = []
in_single = in_double = in_backtick = False
in_line_comment = in_block_comment = False
escape = False
for i, ch in enumerate(s):
    nxt = s[i + 1] if i + 1 < len(s) else ''
    if in_line_comment:
        if ch == '\n':
            in_line_comment = False
        continue
    if in_block_comment:
        if ch == '*' and nxt == '/':
            in_block_comment = False
        continue
    if in_single:
        if escape:
            escape = False
        elif ch == '\\':
            escape = True
        elif ch == "'":
            in_single = False
        continue
    if in_double:
        if escape:
            escape = False
        elif ch == '\\':
            escape = True
        elif ch == '"':
            in_double = False
        continue
    if in_backtick:
        if escape:
            escape = False
        elif ch == '\\':
            escape = True
        elif ch == '`':
            in_backtick = False
        continue
    if ch == '/' and nxt == '/':
        in_line_comment = True
        continue
    if ch == '/' and nxt == '*':
        in_block_comment = True
        continue
    if ch == "'":
        in_single = True
        continue
    if ch == '"':
        in_double = True
        continue
    if ch == '`':
        in_backtick = True
        continue
    if ch == '(':
        stack.append(i)
    elif ch == ')':
        if stack:
            stack.pop()
        else:
            print('extra close at', i)
            break
print('unmatched opens', len(stack))
for pos in stack[-12:]:
    start = max(0, pos - 120)
    end = min(len(s), pos + 220)
    print('\nPOS', pos)
    print(s[start:end])
