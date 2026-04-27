from pathlib import Path

files = [
    Path(r"c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js"),
    Path(r"c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js"),
]

for path in files:
    text = path.read_text(encoding="utf-8")

    old_price = '{key:"piece",label:"Say",align:"right",get:A=>typeof A.sum_piece=="number"?A.sum_piece:"-"},{key:"materials",label:"Xammal",get:A=>A.materials},{key:"printers",label:"Çap",get:A=>A.printers},{key:"price",label:"Qiyməti",align:"right",get:A=>A.prices_per_m2||"-"},{key:"total",label:"Toplam Dəyəri",align:"right",get:A=>A.total_ceiled}'
    new_price = '{key:"piece",label:"İşlərin Sayı",align:"right",get:A=>typeof A.sum_piece=="number"?A.sum_piece:"-"},{key:"materials",label:"Xammal",get:A=>A.materials},{key:"printers",label:"Çap",get:A=>A.printers},{key:"price",label:"Qiyməti",align:"right",get:A=>{const M=Number(A.prices_per_m2);return Number.isFinite(M)?(Number.isInteger(M)?String(M):M.toFixed(2)):(A.prices_per_m2||"-")}},{key:"total",label:"Toplam Dəyəri",align:"right",get:A=>A.total_ceiled}'
    if old_price not in text:
        raise SystemExit(f"price snippet not found in {path}")
    text = text.replace(old_price, new_price, 1)

    marker = 'Tarix: ${A(ye)}</div>`:""}'
    idx = text.find(marker)
    if idx == -1:
        raise SystemExit(f"pdf meta marker not found in {path}")
    start = idx + len(marker)
    end_marker = '</div>                                     </div>'
    end = text.find(end_marker, start)
    if end == -1:
        raise SystemExit(f"pdf meta end marker not found in {path}")
    replacement = '                         <div class="row">İşlərin Sayı: ${R.length}</div>                                   <div class="row">Ümumi: ${Ve} AZN</div>'
    text = text[:start] + replacement + text[end:]

    path.write_text(text, encoding="utf-8")

print('Applied PDF/export fixes in both assets')
