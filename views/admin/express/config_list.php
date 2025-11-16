<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>快递配置管理 - 后台管理</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            font-size: 24px;
            color: #333;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2d8cf0;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #2b85e4;
        }
        
        .btn-success {
            background: #19be6b;
            color: #fff;
        }
        
        .btn-warning {
            background: #ff9900;
            color: #fff;
        }
        
        .btn-danger {
            background: #ed4014;
            color: #fff;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }
        
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e8eaec;
        }
        
        .table th {
            background: #f8f8f9;
            font-weight: 600;
            color: #515a6e;
            font-size: 14px;
        }
        
        .table td {
            color: #333;
            font-size: 14px;
        }
        
        .table tbody tr:hover {
            background: #f8f8f9;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        
        .badge-success {
            background: #e8f9f0;
            color: #19be6b;
        }
        
        .badge-danger {
            background: #fef0f0;
            color: #ed4014;
        }
        
        .badge-warning {
            background: #fff7e6;
            color: #ff9900;
        }
        
        .badge-info {
            background: #e8f4ff;
            color: #2d8cf0;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #808695;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .express-logo {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            background: #2d8cf0;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .config-info {
            font-size: 12px;
            color: #808695;
            margin-top: 5px;
        }
        
        .monthly-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>快递配置管理</h1>
            <a href="/admin/express/configs/create" class="btn btn-primary">+ 新增配置</a>
        </div>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>快递公司</th>
                        <th>合作伙伴ID</th>
                        <th>月结账号</th>
                        <th>发件地址</th>
                        <th>环境</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="configList">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">加载中...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // 加载配置列表
        async function loadConfigs() {
            try {
                const response = await fetch('/admin/express/configs');
                const result = await response.json();
                
                if (result.code === 0) {
                    renderConfigs(result.data);
                } else {
                    document.getElementById('configList').innerHTML = `
                        <tr>
                            <td colspan="7" class="empty-state">
                                加载失败：${result.msg}
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                document.getElementById('configList').innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            加载失败：${error.message}
                        </td>
                    </tr>
                `;
            }
        }
        
        // 渲染配置列表
        function renderConfigs(configs) {
            const tbody = document.getElementById('configList');
            
            if (!configs || configs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div>
                                <p style="font-size: 16px; margin-bottom: 10px;">暂无配置</p>
                                <p>请点击右上角"新增配置"按钮添加快递配置</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = configs.map(config => `
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <span class="express-logo">${config.express_code}</span>
                            <div>
                                <div>${config.express_name}</div>
                                ${config.monthly_account ? '<span class="monthly-badge">月结客户</span>' : ''}
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>${config.partner_id}</div>
                        <div class="config-info">校验码：${maskString(config.checkword)}</div>
                    </td>
                    <td>
                        ${config.monthly_account 
                            ? `<span class="badge badge-success">${config.monthly_account}</span>` 
                            : '<span class="badge badge-warning">未配置</span>'}
                    </td>
                    <td>
                        <div>${config.sender_province} ${config.sender_city} ${config.sender_county}</div>
                        <div class="config-info">${config.sender_address}</div>
                    </td>
                    <td>
                        ${config.sandbox_mode == 1 
                            ? '<span class="badge badge-warning">测试</span>' 
                            : '<span class="badge badge-success">生产</span>'}
                    </td>
                    <td>
                        ${config.status == 1 
                            ? '<span class="badge badge-success">启用</span>' 
                            : '<span class="badge badge-danger">禁用</span>'}
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="/admin/express/configs/edit?id=${config.id}" class="btn btn-primary btn-sm">编辑</a>
                            <button onclick="toggleStatus(${config.id}, ${config.status})" class="btn btn-warning btn-sm">
                                ${config.status == 1 ? '禁用' : '启用'}
                            </button>
                            <button onclick="deleteConfig(${config.id})" class="btn btn-danger btn-sm">删除</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        // 掩码显示敏感信息
        function maskString(str) {
            if (!str || str.length <= 6) return '***';
            return str.substring(0, 3) + '***' + str.substring(str.length - 3);
        }
        
        // 切换状态
        async function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            const action = newStatus == 1 ? '启用' : '禁用';
            
            if (!confirm(`确定要${action}此配置吗？`)) {
                return;
            }
            
            try {
                const response = await fetch('/admin/express/configs/toggle-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, status: newStatus })
                });
                
                const result = await response.json();
                
                if (result.code === 0) {
                    alert(`${action}成功！`);
                    loadConfigs();
                } else {
                    alert(`${action}失败：` + result.msg);
                }
            } catch (error) {
                alert(`${action}失败：` + error.message);
            }
        }
        
        // 删除配置
        async function deleteConfig(id) {
            if (!confirm('确定要删除此配置吗？删除后无法恢复！')) {
                return;
            }
            
            try {
                const response = await fetch('/admin/express/configs/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });
                
                const result = await response.json();
                
                if (result.code === 0) {
                    alert('删除成功！');
                    loadConfigs();
                } else {
                    alert('删除失败：' + result.msg);
                }
            } catch (error) {
                alert('删除失败：' + error.message);
            }
        }
        
        // 页面加载时获取配置列表
        loadConfigs();
    </script>
</body>
</html>
