import re, collections
total_lines = 0
counts = collections.Counter()
in_ajuan = False
with open('docs/prasojo.sql', encoding='utf8', errors='ignore') as f:
    for line in f:
        if line.startswith('INSERT INTO `ajuan`'):
            in_ajuan = True
        elif line.startswith('INSERT INTO'):
            in_ajuan = False
        if in_ajuan and line.startswith('('):
            total_lines += 1
            # more robust regex:
            # (ajuan_id, ajuan_no_reg, ajuan_layanan_kode, ajuan_jenis_ajuan_id, ajuan_pelapor_id, ajuan_pelapor_nik, ajuan_pelapor_kk, ajuan_pelapor_role_id, ajuan_pelapor_role_name, ajuan_is_online, ajuan_is_mandiri, ajuan_status, ...)
            # We can just split by ', ' because string values like 'Mandiri' won't contain ', ' unless we are unlucky.
            # Actually, split by ',' and take the 12th element and strip whitespace.
            # Let's write a small state machine to parse SQL tuples safely.
            tuple_content = line.strip().rstrip(',;')
            if tuple_content.startswith('(') and tuple_content.endswith(')'):
                tuple_content = tuple_content[1:-1]
            
            parts = []
            curr = ''
            in_str = False
            for char in tuple_content:
                if char == "'":
                    in_str = not in_str
                    curr += char
                elif char == ',' and not in_str:
                    parts.append(curr.strip())
                    curr = ''
                else:
                    curr += char
            parts.append(curr.strip())
            
            if len(parts) >= 12:
                status = parts[11].strip("'" )
                counts[status] += 1
            else:
                counts['UNPARSED'] += 1
print(f'Total ajuan lines: {total_lines}')
for k,v in counts.items():
    print(f'{k}: {v}')
