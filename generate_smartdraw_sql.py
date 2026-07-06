import re

dbml_path = r'C:\Users\Amru\.gemini\antigravity-ide\brain\5632492f-9a73-4e84-a485-d207bfb920e3\dbdiagram.md'

with open(dbml_path, 'r', encoding='utf-8') as f:
    dbml = f.read()

sql_lines = []
sql_lines.append('-- SQL for SmartDraw ERD Import\n')

# Parse Tables
table_matches = re.finditer(r'Table\s+(\w+)\s+\{([^}]+)\}', dbml)
for match in table_matches:
    table_name = match.group(1)
    columns_text = match.group(2).strip()
    
    sql_lines.append(f'CREATE TABLE `{table_name}` (')
    
    col_defs = []
    for line in columns_text.split('\n'):
        line = line.strip()
        if not line: continue
        parts = line.split()
        if len(parts) >= 2:
            col_name = parts[0]
            col_type = parts[1]
            col_defs.append(f'  `{col_name}` {col_type}')
            
    sql_lines.append(',\n'.join(col_defs))
    sql_lines.append(');\n')

# Parse Refs
ref_matches = re.finditer(r'Ref:\s*\"(.*?)\"\.\"(.*?)\"\s*([<>])\s*\"(.*?)\"\.\"(.*?)\"', dbml)

for match in ref_matches:
    t1, c1, op, t2, c2 = match.groups()
    
    # if t1.c1 > t2.c2 => t1.c1 is the foreign key, t2.c2 is the primary key
    if op == '>':
        fk_table, fk_col = t1, c1
        pk_table, pk_col = t2, c2
    else:
        fk_table, fk_col = t2, c2
        pk_table, pk_col = t1, c1
        
    sql_lines.append(f'ALTER TABLE `{fk_table}` ADD FOREIGN KEY (`{fk_col}`) REFERENCES `{pk_table}`(`{pk_col}`);')

with open(r'C:\Users\Amru\.gemini\antigravity-ide\brain\5632492f-9a73-4e84-a485-d207bfb920e3\smartdraw_erd.sql', 'w', encoding='utf-8') as f:
    f.write('\n'.join(sql_lines))

print('Done')
