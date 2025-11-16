<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>快递订单列表 - 快递管理</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            padding: 20px;
            border-bottom: 1px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            font-size: 20px;
            color: #333;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .toolbar {
            padding: 20px;
            border-bottom: 1px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 600px;
        }
        
        .search-box select,
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-box input {
            flex: 1;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #1890ff;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #40a9ff;
        }
        
        .btn-success {
            background: #52c41a;
            color: #fff;
        }
        
        .btn-success:hover {
            background: #73d13d;
        }
        
        .btn-warning {
            background: #faad14;
            color: #fff;
        }
        
        .btn-warning:hover {
            background: #ffc53d;
        }
        
        .btn-danger {
            background: #ff4d4f;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #ff7875;
        }
        
        .btn-default {
            background: #fff;
            color: #333;
            border: 1px solid #d9d9d9;
        }
        
        .btn-default:hover {
            border-color: #1890ff;
            color: #1890ff;
        }
        
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
        }
        
        .content {
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }
        
        th {
            background: #fafafa;
            font-weight: 500;
            color: #333;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .status-created {
            background: #e6f7ff;
            color: #1890ff;
        }
        
        .status-ordered {
            background: #fff7e6;
            color: #fa8c16;
        }
        
        .status-transporting {
            background: #f0f5ff;
            color: #2f54eb;
        }
        
        .status-delivered {
            background: #f6ffed;
            color: #52c41a;
        }
        
        .status-cancelled {
            background: #fff1f0;
            color: #ff4d4f;
        }
        
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
        }
        
        .pagination button {
            padding: 6px 12px;
            border: 1px solid #d9d9d9;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .pagination button:hover:not(:disabled) {
            border-color: #1890ff;
            color: #1890ff;
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination .current-page {
            padding: 6px 12px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            font-size: 18px;
            color: #333;
        }
        
        .modal-close {
            cursor: pointer;
            font-size: 24px;
            color: #999;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .waybill-preview {
            width: 100%;
            min-height: 400px;
            border: 1px solid #e8e8e8;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>快递订单列表</h2>
            <div class="header-actions">
                <a href="/admin/express/print-config" class="btn btn-default">打印配置</a>
                <a href="/admin/express/configs" class="btn btn-default">快递配置</a>
            </div>
        </div>
        
        <div class="toolbar">
            <div class="search-box">
                <select id="expressCodeFilter">
                    <option value="">全部快递公司</option>
                    <option value="SF">顺丰速运</option>
                    <option value="YTO">圆通速递</option>
                    <option value="ZTO">中通快递</option>
                    <option value="STO">申通快递</option>
                </select>
                
                <select id="statusFilter">
                    <option value="">全部状态</option>
                    <option value="1">已创建</option>
                    <option value="2">已下单</option>
                    <option value="3">运输中</option>
                    <option value="6">已签收</option>
                    <option value="7">已取消</option>
                </select>
                
                <input type="text" id="keyword" placeholder="搜索订单号/运单号/收件人">
                <button class="btn btn-primary" onclick="searchOrders()">搜索</button>
            </div>
            
            <div>
                <button class="btn btn-success" onclick="batchPrint()">批量打印</button>
            </div>
        </div>
        
        <div class="content">
            <table id="orderTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                        <th>订单号</th>
                        <th>运单号</th>
                        <th>快递公司</th>
                        <th>收件人</th>
                        <th>收件地址</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="orderList">
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                            加载中...
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="pagination">
                <button id="prevPage" onclick="prevPage()">上一页</button>
                <span class="current-page">第 <span id="currentPage">1</span> 页</span>
                <button id="nextPage" onclick="nextPage()">下一页</button>
            </div>
        </div>
    </div>
    
    <!-- 预览模态框 -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>电子面单预览</h3>
                <span class="modal-close" onclick="closePreview()">&times;</span>
            </div>
            <div id="waybillPreview" class="waybill-preview"></div>
            <div style="margin-top: 20px; text-align: center;">
                <button class="btn btn-primary" onclick="printFromPreview()">打印</button>
                <button class="btn btn-default" onclick="closePreview()">关闭</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentPage = 1;
        let pageSize = 20;
        let currentOrderId = null;
        let selectedOrders = [];
        
        // 页面加载时获取订单列表
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });
        
        // 加载订单列表
        function loadOrders() {
            const params = new URLSearchParams({
                express_code: document.getElementById('expressCodeFilter').value,
                status: document.getElementById('statusFilter').value,
                keyword: document.getElementById('keyword').value,
                page: currentPage,
                page_size: pageSize
            });
            
            fetch('/admin/express/orders?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        renderOrders(data.data.list);
                        updatePagination(data.data.total);
                    } else {
                        alert('加载失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('加载失败：' + error.message);
                });
        }
        
        // 渲染订单列表
        function renderOrders(orders) {
            const tbody = document.getElementById('orderList');
            
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 40px; color: #999;">暂无数据</td></tr>';
                return;
            }
            
            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td><input type="checkbox" class="order-checkbox" value="${order.id}"></td>
                    <td>${order.order_no}</td>
                    <td>${order.waybill_no || '-'}</td>
                    <td>${order.express_name}</td>
                    <td>${order.consignee_name}<br>${order.consignee_mobile}</td>
                    <td>${order.consignee_province} ${order.consignee_city} ${order.consignee_county}<br>${order.consignee_address}</td>
                    <td>${getStatusBadge(order.status)}</td>
                    <td>${order.created_at}</td>
                    <td>
                        <div class="actions">
                            ${order.waybill_no ? `
                                <button class="btn btn-sm btn-primary" onclick="printWaybill(${order.id})">打印</button>
                                <button class="btn btn-sm btn-default" onclick="previewWaybill(${order.id})">预览</button>
                                <button class="btn btn-sm btn-default" onclick="exportPDF(${order.id})">导出PDF</button>
                            ` : ''}
                            <button class="btn btn-sm btn-default" onclick="viewDetail(${order.id})">详情</button>
                            ${order.status < 6 ? `
                                <button class="btn btn-sm btn-warning" onclick="queryRoute(${order.id})">查询物流</button>
                            ` : ''}
                            ${order.status === 1 || order.status === 2 ? `
                                <button class="btn btn-sm btn-danger" onclick="cancelOrder(${order.id})">取消</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        // 获取状态徽章
        function getStatusBadge(status) {
            const statusMap = {
                1: { text: '已创建', class: 'status-created' },
                2: { text: '已下单', class: 'status-ordered' },
                3: { text: '已揽件', class: 'status-ordered' },
                4: { text: '运输中', class: 'status-transporting' },
                5: { text: '派送中', class: 'status-transporting' },
                6: { text: '已签收', class: 'status-delivered' },
                7: { text: '已取消', class: 'status-cancelled' }
            };
            
            const s = statusMap[status] || { text: '未知', class: '' };
            return `<span class="status-badge ${s.class}">${s.text}</span>`;
        }
        
        // 搜索订单
        function searchOrders() {
            currentPage = 1;
            loadOrders();
        }
        
        // 上一页
        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                loadOrders();
            }
        }
        
        // 下一页
        function nextPage() {
            currentPage++;
            loadOrders();
        }
        
        // 更新分页
        function updatePagination(total) {
            document.getElementById('currentPage').textContent = currentPage;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage * pageSize >= total;
        }
        
        // 全选/取消全选
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }
        
        // 打印面单
        function printWaybill(id) {
            if (confirm('确认打印该订单的电子面单？')) {
                fetch('/admin/express/print-waybill', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        alert('打印成功！');
                    } else {
                        alert('打印失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('打印失败：' + error.message);
                });
            }
        }
        
        // 批量打印
        function batchPrint() {
            const checkboxes = document.querySelectorAll('.order-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
            
            if (ids.length === 0) {
                alert('请选择要打印的订单');
                return;
            }
            
            if (confirm(`确认批量打印 ${ids.length} 个订单的电子面单？`)) {
                fetch('/admin/express/batch-print-waybill', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        alert(data.msg);
                    } else {
                        alert('批量打印失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('批量打印失败：' + error.message);
                });
            }
        }
        
        // 预览面单
        function previewWaybill(id) {
            currentOrderId = id;
            
            fetch('/admin/express/preview-waybill?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        document.getElementById('waybillPreview').innerHTML = data.data.html;
                        document.getElementById('previewModal').style.display = 'block';
                    } else {
                        alert('预览失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('预览失败：' + error.message);
                });
        }
        
        // 从预览打印
        function printFromPreview() {
            if (currentOrderId) {
                printWaybill(currentOrderId);
            }
        }
        
        // 关闭预览
        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
            currentOrderId = null;
        }
        
        // 导出PDF
        function exportPDF(id) {
            fetch('/admin/express/export-waybill-pdf?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        // 下载PDF
                        window.open(data.data.file_url, '_blank');
                        alert('PDF导出成功！');
                    } else {
                        alert('导出失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('导出失败：' + error.message);
                });
        }
        
        // 查看详情
        function viewDetail(id) {
            window.location.href = '/admin/express/order-detail?id=' + id;
        }
        
        // 查询物流
        function queryRoute(id) {
            fetch('/admin/express/query-route?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        alert('物流信息已更新');
                        loadOrders();
                    } else {
                        alert('查询失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('查询失败：' + error.message);
                });
        }
        
        // 取消订单
        function cancelOrder(id) {
            if (confirm('确认取消该快递订单？')) {
                fetch('/admin/express/cancel-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        alert('取消成功');
                        loadOrders();
                    } else {
                        alert('取消失败：' + data.msg);
                    }
                })
                .catch(error => {
                    alert('取消失败：' + error.message);
                });
            }
        }
    </script>
</body>
</html>
