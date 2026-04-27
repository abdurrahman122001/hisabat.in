from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
print('new_material_card', 'grid grid-cols-1 md:grid-cols-3 gap-4' in s and '"Yarat"' in s)
print('assign_card', 'Mövcud materialı printerə əlavə et' in s)
print('materials_select', 'Material seç' in s)
