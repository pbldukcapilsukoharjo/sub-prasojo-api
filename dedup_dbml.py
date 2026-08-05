import re

artifact_path = r'C:\Users\Amru\.gemini\antigravity-ide\brain\5632492f-9a73-4e84-a485-d207bfb920e3\dbdiagram.md'

with open(artifact_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
seen_relations = set()

for line in lines:
    if line.startswith('Ref:'):
        # Format: Ref: "table1"."col1" > "table2"."col2"
        # Extract everything
        match = re.search(r'Ref:\s*\"(.*?)\"\.\"(.*?)\"\s*([<>-])\s*\"(.*?)\"\.\"(.*?)\"', line)
        if match:
            t1, c1, op, t2, c2 = match.groups()
            
            # Canonicalize: alphabetical order of t1.c1 and t2.c2
            node1 = f'{t1}.{c1}'
            node2 = f'{t2}.{c2}'
            
            if node1 > node2:
                # swap nodes
                node1, node2 = node2, node1
                # invert operator
                if op == '<': op = '>'
                elif op == '>': op = '<'
                
            rel_key = f'{node1}{op}{node2}'
            if rel_key in seen_relations:
                continue # Duplicate relation found!
                
            seen_relations.add(rel_key)
            new_lines.append(line)
        else:
            new_lines.append(line)
    else:
        new_lines.append(line)

with open(artifact_path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print(f"Deduplicated DBML. Total unique relations: {len(seen_relations)}")
