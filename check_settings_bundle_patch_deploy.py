from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
print('state_old', '[ce,ee]=S.useState("")' in s)
print('state_new', '[ce,ee]=S.useState([])' in s)
print('checkbox', 'checked:ce.includes' in s)
print('old_select', 'value:ce,onChange:T=>ee(T.target.value)' in s)
print('submit_categories', 'categories:ce' in s)
