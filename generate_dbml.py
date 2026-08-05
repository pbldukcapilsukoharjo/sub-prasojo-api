import re
import os

sql_file = r'c:\projects\laravel\sub-prasojo-api\docs\prasojo_nodata.sql'
models_dir = r'c:\projects\laravel\sub-prasojo-api\app\Models\Prasojo'

with open(sql_file, 'r', encoding='utf-8') as f:
    sql_content = f.read()

# Parse SQL for tables
tables = {}
current_table = None

lines = sql_content.split('\n')
for line in lines:
    line = line.strip()
    create_match = re.match(r'^CREATE TABLE `(.*?)` \(', line)
    if create_match:
        current_table = create_match.group(1)
        tables[current_table] = []
        continue
    
    if current_table and line.startswith('`'):
        col_match = re.match(r'^`(.*?)`\s+([a-zA-Z0-9_]+)(\(.*?\))?', line)
        if col_match:
            col_name = col_match.group(1)
            col_type = col_match.group(2)
            tables[current_table].append((col_name, col_type))
            
    if current_table and line.startswith(') ENGINE='):
        current_table = None

dbml = ''
for table, cols in tables.items():
    dbml += f'Table {table} {{\n'
    for col_name, col_type in cols:
        dbml += f'  {col_name} {col_type}\n'
    dbml += '}\n\n'


# Now for models relationships
relations = []

for root, dirs, files in os.walk(models_dir):
    for file in files:
        if file.endswith('.php'):
            with open(os.path.join(root, file), 'r', encoding='utf-8') as f:
                content = f.read()
                
                # Find table name
                table_match = re.search(r'protected\s+\$table\s*=\s*[\'\"](.*?)[\'\"];', content)
                if not table_match:
                    continue
                current_table = table_match.group(1)
                
                # Find relationships: belongsTo, hasMany, hasOne
                # e.g. return $this->belongsTo(Admin::class, 'log_admin_id', 'id');
                rel_matches = re.finditer(r'return\s+\$this->(belongsTo|hasMany|hasOne)\s*\(\s*([A-Za-z0-9_]+)::class\s*(,\s*[\'\"](.*?)[\'\"]\s*(,\s*[\'\"](.*?)[\'\"])?)?', content)
                for match in rel_matches:
                    rel_type = match.group(1)
                    target_model = match.group(2)
                    fk = match.group(4)
                    pk = match.group(6)
                    
                    relations.append((current_table, rel_type, target_model, fk, pk))

model_to_table = {}
for root, dirs, files in os.walk(models_dir):
    for file in files:
        if file.endswith('.php'):
            with open(os.path.join(root, file), 'r', encoding='utf-8') as f:
                content = f.read()
                model_name = file.replace('.php', '')
                table_match = re.search(r'protected\s+\$table\s*=\s*[\'\"](.*?)[\'\"];', content)
                if table_match:
                    model_to_table[model_name] = table_match.group(1)

# Add relations to DBML
for rel in relations:
    src_table = rel[0]
    rel_type = rel[1]
    target_model = rel[2]
    fk = rel[3]
    pk = rel[4]
    
    target_table = model_to_table.get(target_model, target_model)
    
    # DBML Relation Syntax:
    # 1-to-many: Ref: "users"."id" < "posts"."user_id"
    # many-to-1: Ref: "posts"."user_id" > "users"."id"
    # 1-to-1: Ref: "users"."id" - "profiles"."user_id"
    
    # Determine default foreign key name if not specified
    if not fk:
        if rel_type == 'belongsTo':
            # e.g. User belongsTo Role -> fk is role_id on User table
            fk = target_table.lower() + '_id'
        else:
            # hasMany / hasOne
            # e.g. User hasMany Post -> fk is user_id on Post table
            fk = src_table.lower() + '_id'
            
    if not pk:
        pk = 'id'
        
    if rel_type == 'belongsTo':
        # src_table has the FK
        dbml += f'Ref: "{src_table}"."{fk}" > "{target_table}"."{pk}"\n'
    elif rel_type == 'hasMany':
        # target_table has the FK
        dbml += f'Ref: "{src_table}"."{pk}" < "{target_table}"."{fk}"\n'
    elif rel_type == 'hasOne':
        # target_table has the FK
        dbml += f'Ref: "{src_table}"."{pk}" - "{target_table}"."{fk}"\n'

print(dbml)
