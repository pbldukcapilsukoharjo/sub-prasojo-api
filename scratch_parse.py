import re
statuses = set()
in_ajuan = False
with open('docs/prasojo.sql', encoding='utf8', errors='ignore') as f:
    for line in f:
        if line.startswith('INSERT INTO `ajuan`'):
            in_ajuan = True
        elif line.startswith('INSERT INTO'):
            in_ajuan = False
        if in_ajuan and line.startswith('('):
            # parse the CSV like structure naively
            # format is (ajuan_id, ajuan_no_reg, ajuan_layanan_kode, ajuan_jenis_ajuan_id, ajuan_pelapor_id, ajuan_pelapor_nik, ajuan_pelapor_kk, ajuan_pelapor_role_id, ajuan_pelapor_role_name, ajuan_is_online, ajuan_is_mandiri, ajuan_status, ...)
            # split by comma, but some strings have commas inside. So just use a simple regex for the first 12 fields
            m = re.search(r'^\(\d+,\s*\'[^\']*\',\s*\'[^\']*\',\s*\d+,\s*\d+,\s*\'[^\']*\',\s*\'[^\']*\',\s*\d+,\s*\'[^\']*\',\s*\d+,\s*\d+,\s*\'([^\']+)\'', line)
            if m:
                statuses.add(m.group(1))
            else:
                m2 = re.search(r'^\(\d+,\s*\'[^\']*\',\s*\'[^\']*\',\s*\d+,\s*\d+,\s*(NULL|\'[^\']*\'),\s*(NULL|\'[^\']*\'),\s*\d+,\s*(NULL|\'[^\']*\'),\s*\d+,\s*\d+,\s*(NULL|\'([^\']+)\')', line)
                if m2:
                    val = m2.group(4)
                    if val and val != 'NULL':
                        statuses.add(val.strip('\''))
print(statuses)
