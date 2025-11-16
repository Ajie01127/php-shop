<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打印配置 - 快递管理</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            padding: 20px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .header h2 {
            font-size: 20px;
            color: #333;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group .help-text {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #999;
        }
        
        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #1890ff;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
            cursor: pointer;
        }
        
        .radio-group input[type="radio"] {
            margin-right: 6px;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 6px;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #1890ff;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #40a9ff;
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
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #52c41a;
        }
        
        .alert-error {
            background: #fff2f0;
            border: 1px solid #ffccc7;
            color: #ff4d4f;
        }
        
        .alert-info {
            background: #e6f7ff;
            border: 1px solid #91d5ff;
            color: #1890ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>电子面单打印配置</h2>
        </div>
        
        <div class="content">
            <div id="message" style="display: none;"></div>
            
            <form id="printConfigForm">
                <div class="form-group">
                    <label>打印模式</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="print_mode" value="local" checked>
                            本地打印机
                        </label>
                        <label>
                            <input type="radio" name="print_mode" value="pdf">
                            PDF导出
                        </label>
                        <label>
                            <input type="radio" name="print_mode" value="image">
                            图片导出
                        </label>
                        <label>
                            <input type="radio" name="print_mode" value="preview">
                            仅预览
                        </label>
                    </div>
                    <span class="help-text">选择打印方式，建议使用本地打印机模式</span>
                </div>
                
                <div class="form-group" id="printerGroup">
                    <label>默认打印机</label>
                    <select name="printer_name" id="printerSelect">
                        <option value="">系统默认打印机</option>
                    </select>
                    <span class="help-text">选择要使用的打印机，留空则使用系统默认打印机</span>
                </div>
                
                <div class="form-group">
                    <label>面单模板尺寸</label>
                    <select name="template_size">
                        <option value="100,150" selected>100mm x 150mm（标准快递面单）</option>
                        <option value="100,100">100mm x 100mm（正方形面单）</option>
                        <option value="76,130">76mm x 130mm（小尺寸面单）</option>
                    </select>
                    <span class="help-text">选择面单打印尺寸，需与实际使用的热敏纸尺寸匹配</span>
                </div>
                
                <div class="form-group">
                    <label>默认打印份数</label>
                    <input type="number" name="print_copies" value="1" min="1" max="10">
                    <span class="help-text">每个订单默认打印的份数</span>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="auto_print" value="1">
                            自动打印（下单成功后自动打印）
                        </label>
                    </div>
                    <span class="help-text">开启后，快递订单创建成功将自动发送到打印机</span>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="save_pdf" value="1" checked>
                            自动保存PDF备份
                        </label>
                    </div>
                    <span class="help-text">开启后，每次打印都会保存PDF文件作为备份</span>
                </div>
                
                <div class="form-group">
                    <label>PDF保存路径</label>
                    <input type="text" name="pdf_path" value="/storage/express/pdf/" placeholder="/storage/express/pdf/">
                    <span class="help-text">PDF文件保存的服务器路径</span>
                </div>
                
                <div class="form-group">
                    <label>批量打印间隔（秒）</label>
                    <input type="number" name="print_interval" value="1" min="0" max="10">
                    <span class="help-text">批量打印时每个订单之间的延迟时间</span>
                </div>
                
                <div class="form-group">
                    <label>最大批量打印数量</label>
                    <input type="number" name="max_batch_size" value="50" min="1" max="100">
                    <span class="help-text">一次性允许打印的最大订单数量</span>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">保存配置</button>
                    <button type="button" class="btn btn-default" onclick="resetConfig()">恢复默认</button>
                    <button type="button" class="btn btn-default" onclick="refreshPrinters()">刷新打印机列表</button>
                    <button type="button" class="btn btn-default" onclick="history.back()">返回</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // 页面加载时获取打印机列表
        document.addEventListener('DOMContentLoaded', function() {
            loadPrinters();
            loadConfig();
            
            // 打印模式切换时显示/隐藏打印机选择
            document.querySelectorAll('input[name="print_mode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const printerGroup = document.getElementById('printerGroup');
                    printerGroup.style.display = this.value === 'local' ? 'block' : 'none';
                });
            });
        });
        
        // 加载打印机列表
        function loadPrinters() {
            fetch('/admin/express/printers')
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0) {
                        const select = document.getElementById('printerSelect');
                        select.innerHTML = '<option value="">系统默认打印机</option>';
                        
                        data.data.forEach(printer => {
                            const option = document.createElement('option');
                            option.value = printer;
                            option.textContent = printer;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('获取打印机列表失败:', error);
                });
        }
        
        // 加载配置
        function loadConfig() {
            fetch('/admin/express/print-config/get')
                .then(response => response.json())
                .then(data => {
                    if (data.code === 0 && data.data) {
                        const config = data.data;
                        
                        // 设置打印模式
                        const printModeRadios = document.querySelectorAll('input[name="print_mode"]');
                        printModeRadios.forEach(radio => {
                            if (radio.value === config.print_mode) {
                                radio.checked = true;
                                // 触发change事件以显示/隐藏打印机选择
                                radio.dispatchEvent(new Event('change'));
                            }
                        });
                        
                        // 设置打印机
                        if (config.printer_name) {
                            const printerSelect = document.getElementById('printerSelect');
                            // 等待打印机列表加载后再设置
                            setTimeout(() => {
                                printerSelect.value = config.printer_name;
                            }, 500);
                        }
                        
                        // 设置模板尺寸
                        document.querySelector('select[name="template_size"]').value = config.template_size;
                        
                        // 设置打印份数
                        document.querySelector('input[name="print_copies"]').value = config.print_copies;
                        
                        // 设置复选框
                        document.querySelector('input[name="auto_print"]').checked = config.auto_print == 1;
                        document.querySelector('input[name="save_pdf"]').checked = config.save_pdf == 1;
                        
                        // 设置PDF路径
                        document.querySelector('input[name="pdf_path"]').value = config.pdf_path;
                        
                        // 设置批量打印参数
                        if (config.print_interval !== undefined) {
                            document.querySelector('input[name="print_interval"]').value = config.print_interval;
                        }
                        if (config.max_batch_size !== undefined) {
                            document.querySelector('input[name="max_batch_size"]').value = config.max_batch_size;
                        }
                    }
                })
                .catch(error => {
                    console.error('加载配置失败:', error);
                });
        }
        
        // 刷新打印机列表
        function refreshPrinters() {
            showMessage('正在刷新打印机列表...', 'info');
            loadPrinters();
            setTimeout(() => {
                showMessage('打印机列表已刷新', 'success');
            }, 500);
        }
        
        // 恢复默认配置
        function resetConfig() {
            if (!confirm('确认要恢复默认配置吗？')) {
                return;
            }
            
            showMessage('正在恢复默认配置...', 'info');
            
            fetch('/admin/express/print-config/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 0) {
                    showMessage('配置已恢复为默认值', 'success');
                    // 重新加载配置
                    setTimeout(() => {
                        loadConfig();
                    }, 1000);
                } else {
                    showMessage('恢复失败：' + data.msg, 'error');
                }
            })
            .catch(error => {
                showMessage('恢复失败：' + error.message, 'error');
            });
        }
        
        // 提交表单
        document.getElementById('printConfigForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const config = {};
            
            // 收集表单数据
            config.print_mode = formData.get('print_mode');
            config.printer_name = formData.get('printer_name') || null;
            config.template_size = formData.get('template_size');
            config.print_copies = parseInt(formData.get('print_copies'));
            config.auto_print = formData.get('auto_print') ? 1 : 0;
            config.save_pdf = formData.get('save_pdf') ? 1 : 0;
            config.pdf_path = formData.get('pdf_path');
            config.print_interval = parseInt(formData.get('print_interval'));
            config.max_batch_size = parseInt(formData.get('max_batch_size'));
            
            // 显示保存中提示
            showMessage('正在保存配置...', 'info');
            
            // 保存到数据库
            fetch('/admin/express/print-config/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 0) {
                    showMessage('配置保存成功！', 'success');
                } else {
                    showMessage('配置保存失败：' + data.msg, 'error');
                }
            })
            .catch(error => {
                showMessage('配置保存失败：' + error.message, 'error');
            });
        });
        
        // 显示消息
        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.className = 'alert alert-' + type;
            messageDiv.textContent = text;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
