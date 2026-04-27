from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

for path in paths:
    s = path.read_text(encoding='utf-8')

    old_state = '[R,H]=S.useState(""),[W,J]=S.useState(""),[ce,ee]=S.useState([]),P=S.useCallback('
    new_state = '[R,H]=S.useState(""),[W,J]=S.useState(""),[ce,ee]=S.useState([]),[qe,Ke]=S.useState(""),[Ve,Be]=S.useState(""),P=S.useCallback('
    if old_state not in s:
        raise SystemExit(f'state snippet not found in {path}')
    s = s.replace(old_state, new_state, 1)

    old_handler = '},I=async()=>{var T,N,L;r(!0),i(""),l("");try{const w=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,categories:ce,category:ce[0]||"",status:1})}),O=await w.json().catch(()=>null);if(!w.ok||!(O!=null&&O.ok)){i((O==null?void 0:O.error)||(O==null?void 0:O.db_error)||((T=O==null?void 0:O.errors)==null?void 0:T.key)||((N=O==null?void 0:O.errors)==null?void 0:N.label)||((L=O==null?void 0:O.errors)==null?void 0:L.category)||"Xəta baş verdi");return}H(""),J(""),ee([]),l("Material yaradıldı"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}},C=async(T,N)=>{'
    new_handler = '},I=async()=>{var T,N,L;r(!0),i(""),l("");try{const w=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,categories:ce,category:ce[0]||"",status:1})}),O=await w.json().catch(()=>null);if(!w.ok||!(O!=null&&O.ok)){i((O==null?void 0:O.error)||(O==null?void 0:O.db_error)||((T=O==null?void 0:O.errors)==null?void 0:T.key)||((N=O==null?void 0:O.errors)==null?void 0:N.label)||((L=O==null?void 0:O.errors)==null?void 0:L.category)||"Xəta baş verdi");return}H(""),J(""),ee([]),l("Material yaradıldı"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}},q=async()=>{var T,N,L;r(!0),i(""),l("");try{const w=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:qe,label:Ve,categories:ce,category:ce[0]||"",status:1})}),O=await w.json().catch(()=>null);if(!w.ok||!(O!=null&&O.ok)){i((O==null?void 0:O.error)||(O==null?void 0:O.db_error)||((T=O==null?void 0:O.errors)==null?void 0:T.key)||((N=O==null?void 0:O.errors)==null?void 0:N.label)||((L=O==null?void 0:O.errors)==null?void 0:L.category)||"Xəta baş verdi");return}Ke(""),Be(""),ee([]),l("Material printerə əlavə edildi"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}},C=async(T,N)=>{'
    if old_handler not in s:
        raise SystemExit(f'handler snippet not found in {path}')
    s = s.replace(old_handler, new_handler, 1)

    s = s.replace('value:R,onChange:T=>{const N=T.target.value,L=p.find(w=>String((w==null?void 0:w.key)??"")===N);H(N),J(String((L==null?void 0:L.label)??""))}', 'value:qe,onChange:T=>{const N=T.target.value,L=p.find(w=>String((w==null?void 0:w.key)??"")===N);Ke(N),Be(String((L==null?void 0:L.label)??""))}', 1)
    s = s.replace('o.jsx(z,{label:"key",value:R,onChange:T=>H(T.target.value)})', 'o.jsx(z,{label:"key",value:qe,onChange:T=>Ke(T.target.value)})', 1)
    s = s.replace('o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)})', 'o.jsx(z,{label:"label",value:Ve,onChange:T=>Be(T.target.value)})', 1)
    s = s.replace('onClick:I,disabled:n||ce.length===0||!R,children:[o.jsx(Fs,{size:16}),"Əlavə et"]', 'onClick:q,disabled:n||ce.length===0||!qe,children:[o.jsx(Fs,{size:16}),"Əlavə et"]', 1)

    path.write_text(s, encoding='utf-8')
    print('patched', path)
