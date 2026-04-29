#!/usr/bin/env python3
import os
import re

files = [
    'ui/assets/index-CC2b_5k0.js',
    'deploy_hesabat_root/assets/index-CC2b_5k0.js'
]

for file_path in files:
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        continue
    
    print(f"\nChecking {file_path}...")
    
    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    # Count patterns
    patterns = [
        ('"/api/', 'double-quoted'),
        ("'/api/", 'single-quoted'),
        ('`/api/', 'template literal'),
        ('(/api/', 'parenthesis'),
    ]
    
    total = 0
    for pattern, name in patterns:
        count = content.count(pattern)
        if count > 0:
            print(f"  Found {count} {name} /api/ references")
            total += count
    
    if total == 0:
        print("  No absolute /api/ paths found - file is patched!")
        continue
    
    # Patch the file
    print(f"  Patching {total} references...")
    
    # Replace various patterns
    content = content.replace('"/api/', '"api/')
    content = content.replace("'/api/", "'api/")
    content = content.replace('`/api/', '`api/')
    content = content.replace('(/api/', '(api/')
    
    with open(file_path, 'w', encoding='utf-8', errors='ignore') as f:
        f.write(content)
    
    print(f"  Patched successfully!")

print("\nDone! Clear browser cache and try again.")
