from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_state = 'laser_cut_wood:"",laser_cut_forex:"",laser_cut_orch:"",laser_graw_wood:"",laser_graw_cut_forex:"",laser_graw_cut_orch:""}),[n,r]=S.useState({}),[s,i]=S.useState(!1),[a,l]=S.useState(""),[c,u]=S.useState(""),[d,f]=S.useState(""),p=['
new_state = 'laser_cut_wood:"",laser_cut_forex:"",laser_cut_orch:"",laser_graw_wood:"",laser_graw_cut_forex:"",laser_graw_cut_orch:"",prices:{}}),[n,r]=S.useState({}),[s,i]=S.useState(!1),[a,l]=S.useState(""),[c,u]=S.useState(""),[d,f]=S.useState(""),[N,L]=S.useState([]),[w,O]=S.useState([]),p=['

old_count = 'x=h=>{let b=0;for(const _ of p){const E=h[_];if(E==null||String(E).trim()==="")continue;const k=Number(y(String(E)));!Number.isNaN(k)&&k>0&&b++}return b};'
new_count = 'x=h=>{let b=0;for(const _ of p){const E=h[_];if(E==null||String(E).trim()==="")continue;const k=Number(y(String(E)));!Number.isNaN(k)&&k>0&&b++}const E=h.prices||{};for(const k of Object.keys(E)){const R=E[k];if(!R||typeof R!="object")continue;for(const H of Object.keys(R)){const W=R[H];if(W==null||String(W).trim()==="")continue;const J=Number(y(String(W)));!Number.isNaN(J)&&J>0&&b++}}return b};'

old_effect = 'S.useEffect(()=>{j()},[j]);const g=(h,b)=>{const _=p.includes(h)?v(b):b;t(E=>({...E,[h]:_})),n[h]&&r(E=>{const k={...E};return delete k[h],k})},m=async h=>{'
new_effect = 'S.useEffect(()=>{j()},[j]);const ne=S.useMemo(()=>({konica:new Set(["banner_matt","banner_glossy","vinily_ch","vinily_eu","banner_black_mate","banner_black_glossy","white_banner","white_vinily","backlead","flex","banner_440_white","banner_440_black"]),roland:new Set(["banner_matt","banner_glossy","vinily_ch","vinily_eu","black_matt","black_glossy"]),laser:new Set(["cut_wood","cut_forex","cut_orch","graw_wood","graw_forex","graw_orch"])}),[]),ce=S.useCallback(async()=>{try{const[h,b]=await Promise.all([fetch("/hesabat/api/printers_list.php"),fetch("/hesabat/api/materials_list.php")]),_=await h.json().catch(()=>null),E=await b.json().catch(()=>null);h.ok&&(_!=null&&_.ok)&&Array.isArray(_==null?void 0:_.printers)&&L(_.printers.filter(k=>Number((k==null?void 0:k.status)??1)===1)),b.ok&&(E!=null&&E.ok)&&Array.isArray(E==null?void 0:E.materials)&&O(E.materials.filter(k=>Number((k==null?void 0:k.status)??1)===1))}catch{}},[]),ue=S.useMemo(()=>{const h={};for(const b of N){const _=(String((b==null?void 0:b.price_key)??"").trim().toLowerCase()||String((b==null?void 0:b.name)??"").trim().toLowerCase());if(!_)continue;const E=w.filter(k=>{const R=String((k==null?void 0:k.category)??"").trim().toLowerCase(),H=String((k==null?void 0:k.key)??"");return R===_&&!(ne[_]&&ne[_].has(H))});E.length>0&&(h[_]={name:String((b==null?void 0:b.name)??_),materials:E})}return h},[N,w,ne]);S.useEffect(()=>{ce()},[ce]);const g=(h,b)=>{const _=p.includes(h)?v(b):b;t(E=>({...E,[h]:_})),n[h]&&r(E=>{const k={...E};return delete k[h],k})},pe=(h,b,_)=>{const E=v(_),k=`prices.${h}.${b}`;t(R=>({...R,prices:{...(R.prices||{}),[h]:{...((R.prices||{})[h]||{}),[b]:E}}})),n[k]&&r(R=>{const H={...R};return delete H[k],H})},m=async h=>{'

old_submit_reset = 'k!=null&&k.client_id&&u(k.client_id),f("Müştəri uğurla əlavə olundu"),j(),t(R=>({...R,name:"",email:"",phone:""}))'
new_submit_reset = 'k!=null&&k.client_id&&u(k.client_id),f("Müştəri uğurla əlavə olundu"),j(),t(R=>({...R,name:"",email:"",phone:"",prices:{}}))'

old_render_anchor = 'o.jsx("div",{className:"pt-4 flex justify-end",children:o.jsx(fe,{size:"lg",className:"px-10",type:"submit",disabled:s,children:"Əlavə et"})})'
new_render_anchor = 'Object.keys(ue).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(ue).map(([h,b])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-violet-500"}),b.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:b.materials.map(_=>o.jsx(z,{label:_.label,placeholder:"Qiymət",inputMode:"decimal",value:((e.prices||{})[h]||{})[_.key]??"",onChange:E=>pe(h,_.key,E.target.value)},`${h}-${_.key}`))})]},h))}),o.jsx("div",{className:"pt-4 flex justify-end",children:o.jsx(fe,{size:"lg",className:"px-10",type:"submit",disabled:s,children:"Əlavə et"})})'

for path in files:
    s = path.read_text(encoding='utf-8')
    for old, new, label in [
        (old_state, new_state, 'state'),
        (old_count, new_count, 'count'),
        (old_effect, new_effect, 'effect'),
        (old_submit_reset, new_submit_reset, 'reset'),
        (old_render_anchor, new_render_anchor, 'render'),
    ]:
        if old not in s:
            raise SystemExit(f'{label} target not found in {path}')
        s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
