const fs = require('fs');
const filePath = './docs/api-1.json';
const doc = JSON.parse(fs.readFileSync(filePath, 'utf8'));

function getModuleName(path, method) {
    if (path.includes('lembar-kerja')) return 'lembar kerja';
    if (path.includes('ajuan/chart')) return 'chart ajuan';
    if (path.includes('ajuan')) return 'ajuan';
    if (path.includes('produk')) return 'produk';
    if (path.includes('distribusi')) return 'distribusi wilayah';
    if (path.includes('sla/kpi')) return 'KPI SLA';
    if (path.includes('ulasan/kpi')) return 'KPI ulasan';
    if (path.includes('operator/kpi-global')) return 'KPI global operator';
    if (path.includes('operator/peringkat')) return 'peringkat operator';
    if (path.includes('/operator/') && path.includes('/kpi')) return 'detail KPI operator';
    if (path.includes('/operator/') && path.includes('/riwayat')) return 'riwayat operator';
    if (path.includes('dashboard/kpi')) return 'KPI dashboard';
    if (path.includes('dashboard/chart-trend')) return 'Chart Trend';
    if (path.includes('dashboard/top-wilayah')) return 'top wilayah';
    if (path.includes('auth/profile') && method === 'get') return 'profil pengguna';
    if (path.includes('auth/profile') && method === 'put') return 'pembaruan profil';
    if (path.includes('auth/login')) return 'login';
    if (path.includes('auth/register')) return 'registrasi';
    if (path.includes('auth/logout')) return 'logout';
    if (path.includes('export')) return 'export data';
    if (path.includes('filter')) return 'filter ' + path.split('/').pop();
    
    // Default fallback
    const parts = path.split('/').filter(p => p && p !== 'api' && p !== 'v1');
    return parts.join(' ');
}

for (const [path, methods] of Object.entries(doc.paths)) {
  for (const [method, details] of Object.entries(methods)) {
    if (details.responses) {
      for (const [statusStr, res] of Object.entries(details.responses)) {
        if (!res.content || !res.content['application/json']) {
            res.content = { 'application/json': { schema: { type: 'object' } } };
        }
        
        let exObj = {};
        const exStr = res.content['application/json'].example;
        if (exStr) {
            try {
                exObj = JSON.parse(exStr);
            } catch(e) {}
        }

        const statusCode = parseInt(statusStr, 10);
        
        if (statusCode === 200 || statusCode === 201) {
            // Standarisasi Success
            exObj.status = true;
            exObj.code = statusCode;
            
            const moduleName = getModuleName(path, method);
            
            if (!exObj.message || exObj.message === 'Berhasil' || exObj.message === 'Berhasil mengambil data' || exObj.message === 'OK') {
                if (method === 'post' || path.includes('register')) {
                     exObj.message = `Berhasil melakukan ${moduleName}`;
                } else if (method === 'put' || method === 'patch') {
                     exObj.message = `${moduleName} berhasil diperbarui`;
                } else if (method === 'delete') {
                     exObj.message = `${moduleName} berhasil dihapus`;
                } else {
                     exObj.message = `Berhasil mengambil data ${moduleName}`;
                }
            }
            
            if (exObj.data === undefined) {
                exObj.data = null;
            }
        } 
        else if (statusCode === 400) {
            exObj = {
                status: false,
                code: 400,
                message: "Validasi gagal. Silakan periksa kembali input Anda.",
                data: {
                    "nama_parameter": ["Pesan error spesifik dari backend (bahasa indonesia)"]
                }
            };
        }
        else if (statusCode === 401) {
            exObj = {
                status: false,
                code: 401,
                message: "Akses ditolak. Sesi Anda tidak valid atau telah berakhir.",
                data: null
            };
        }
        else if (statusCode === 403) {
            exObj = {
                status: false,
                code: 403,
                message: "Akses dilarang. Anda tidak memiliki izin.",
                data: null
            };
        }
        else if (statusCode === 404) {
            exObj = {
                status: false,
                code: 404,
                message: "Data tidak ditemukan.",
                data: null
            };
        }
        else if (statusCode === 500) {
            exObj = {
                status: false,
                code: 500,
                message: "Terjadi kesalahan pada server.",
                data: {
                    "error": "Pesan error internal (hanya tampil di mode debug/development)"
                }
            };
        }

        // Apply updated example back as string
        res.content['application/json'].example = JSON.stringify(exObj, null, 2) + '\n';
        
        // Also ensure schema doesn't have hardcoded weird examples
        const schema = res.content['application/json'].schema;
        if (schema && schema.properties && schema.properties.code) {
             schema.properties.code.example = statusCode;
             if (schema.properties.code.default !== undefined) {
                 delete schema.properties.code.default;
             }
        }
      }
    }
  }
}

fs.writeFileSync(filePath, JSON.stringify(doc, null, 4));
console.log('Successfully standardized docs/api-1.json');
