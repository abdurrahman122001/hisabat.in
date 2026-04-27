from pathlib import Path
import subprocess, json
path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
script = f"const fs=require('fs'); const src=fs.readFileSync({json.dumps(str(path))},'utf8'); try{{ new Function(src); console.log('OK'); }}catch(e){{ console.log('NAME', e.name); console.log('MSG', e.message); console.log('STACK', e.stack); }}"
result = subprocess.run(['node','-e',script], capture_output=True, text=True)
print(result.stdout)
print(result.stderr)
