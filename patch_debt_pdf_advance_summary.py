from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'y=S.useCallback((x,j,g)=>{const m=window.open("","_blank");if(!m){u("PDF üçün pəncərə açıla bilmədi. Popup icazəsini yoxlayın.");return}const h=ee=>String(ee??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#39;"),b=j.reduce((ee,P)=>{const X=parseFloat(String((P==null?void 0:P.outstanding_debit)??"").replace(",","."));return ee+(Number.isFinite(X)?X:0)},0),_=g!=="warning"&&(n||s),R=`${n||""} - ${s||""}`,H=n&&s&&n===s?`Tarix: ${n}`:`Tarix aralığı: ${R}`,W=g==="warning"?`\n      <tr>\n        <th>ID</th>\n        <th>Müştəri adı</th>\n        <th>Telefon</th>\n        <th>Borc</th>\n      </tr>\n    `:`\n      <tr>\n        <th>ID</th>\n        <th>Müştəri adı</th>\n        <th>Telefon</th>\n        <th>Email</th>\n        <th>Borc</th>\n        <th>Avans</th>\n        <th>Ümumi əməliyyat</th>\n      </tr>\n    `,J=j.map(ee=>g==="warning"?`\n            <tr>\n              <td>${h(ee.client_id)}</td>\n              <td>${h(ee.name)}</td>\n              <td>${h(ee.phone)}</td>\n              <td class="num">${h(ee.outstanding_debit)}</td>\n            </tr>\n          `:`\n          <tr>\n            <td>${h(ee.client_id)}</td>\n            <td>${h(ee.name)}</td>\n            <td>${h(ee.phone)}</td>\n            <td>${h(ee.email)}</td>\n            <td class="num">${h(ee.outstanding_debit)}</td>\n            <td class="num">${h(ee.advanced)}</td>\n            <td class="num">${h(ee.total_amount)}</td>\n          </tr>\n        `).join(""),ce=`\n      <!doctype html>\n      <html>\n        <head>\n          <meta charset="utf-8" />\n          <title>${h(x)}</title>'
new = 'y=S.useCallback((x,j,g)=>{const m=window.open("","_blank");if(!m){u("PDF üçün pəncərə açıla bilmədi. Popup icazəsini yoxlayın.");return}const h=ee=>String(ee??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#39;"),b=j.reduce((ee,P)=>{const X=parseFloat(String((g==="advance"?(P==null?void 0:P.advanced):(P==null?void 0:P.outstanding_debit))??"").replace(",","."));return ee+(Number.isFinite(X)?X:0)},0),_=g!=="warning"&&(n||s),R=`${n||""} - ${s||""}`,H=n&&s&&n===s?`Tarix: ${n}`:`Tarix aralığı: ${R}`,W=g==="warning"?`\n      <tr>\n        <th>ID</th>\n        <th>Müştəri adı</th>\n        <th>Telefon</th>\n        <th>Borc</th>\n      </tr>\n    `:g==="advance"?`\n      <tr>\n        <th>ID</th>\n        <th>Müştəri adı</th>\n        <th>Telefon</th>\n        <th>Email</th>\n        <th>Avans</th>\n        <th>Ümumi əməliyyat</th>\n      </tr>\n    `:`\n      <tr>\n        <th>ID</th>\n        <th>Müştəri adı</th>\n        <th>Telefon</th>\n        <th>Email</th>\n        <th>Borc</th>\n        <th>Avans</th>\n        <th>Ümumi əməliyyat</th>\n      </tr>\n    `,J=j.map(ee=>g==="warning"?`\n            <tr>\n              <td>${h(ee.client_id)}</td>\n              <td>${h(ee.name)}</td>\n              <td>${h(ee.phone)}</td>\n              <td class="num">${h(ee.outstanding_debit)}</td>\n            </tr>\n          `:g==="advance"?`\n          <tr>\n            <td>${h(ee.client_id)}</td>\n            <td>${h(ee.name)}</td>\n            <td>${h(ee.phone)}</td>\n            <td>${h(ee.email)}</td>\n            <td class="num">${h(ee.advanced)}</td>\n            <td class="num">${h(ee.total_amount)}</td>\n          </tr>\n        `:`\n          <tr>\n            <td>${h(ee.client_id)}</td>\n            <td>${h(ee.name)}</td>\n            <td>${h(ee.phone)}</td>\n            <td>${h(ee.email)}</td>\n            <td class="num">${h(ee.outstanding_debit)}</td>\n            <td class="num">${h(ee.advanced)}</td>\n            <td class="num">${h(ee.total_amount)}</td>\n          </tr>\n        `).join(""),ce=`\n      <!doctype html>\n      <html>\n        <head>\n          <meta charset="utf-8" />\n          <title>${h(x)}</title>'

old_summary = '<div class="row">Ümumi borc: ${b.toFixed(2)}</div>'
new_summary = '<div class="row">${g==="advance"?"Ümumi avans":"Ümumi borc"}: ${b.toFixed(2)}</div>'

old_button = 'onClick:()=>y("Avansı olan müştərilər",N,"all")'
new_button = 'onClick:()=>y("Avansı olan müştərilər",N,"advance")'

for path in files:
    s = path.read_text(encoding='utf-8')
    for old_part, new_part, label in [
        (old, new, 'pdf-callback'),
        (old_summary, new_summary, 'summary-line'),
        (old_button, new_button, 'advance-button'),
    ]:
        if old_part not in s:
            raise SystemExit(f'{label} target not found in {path}')
        s = s.replace(old_part, new_part, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
