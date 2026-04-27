from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
out = []
for needle, before, after in [
    ('/hesabat/api/create_work.php', 8000, 16000),
    ('const Gt=', 4000, 12000),
    ('Gt=(', 4000, 12000),
    ('const Kn=', 4000, 12000),
    ('Kn=(', 4000, 12000),
    ('price_per_m2:Z||Y[0].price_per_m2', 3000, 5000),
    ('price_per_m2:bi||q[U].price_per_m2', 3000, 5000),
    ('price_per_m2:we||q[U].price_per_m2', 3000, 5000),
]:
    idx = s.find(needle)
    out.append(f'NEEDLE: {needle} IDX: {idx}\n')
    if idx != -1:
        out.append(s[max(0, idx-before):min(len(s), idx+after)])
    out.append('\n' + '='*120 + '\n')
Path(r'c:\xampp\htdocs\Hesabat\work_add_snippets.txt').write_text(''.join(out), encoding='utf-8')
print('written')
