<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>快递配置 - 后台管理</title>
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
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            padding: 20px 30px;
            border-bottom: 1px solid #e8eaec;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 20px;
            color: #333;
        }
        
        .back-btn {
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #dcdee2;
            border-radius: 4px;
            color: #515a6e;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            border-color: #2d8cf0;
            color: #2d8cf0;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2d8cf0;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group.full {
            flex: 100%;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #515a6e;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-label .required {
            color: #ed4014;
            margin-left: 3px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdee2;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            transition: all 0.3s;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #2d8cf0;
            box-shadow: 0 0 0 2px rgba(45, 140, 240, 0.1);
        }
        
        .form-textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-help {
            font-size: 12px;
            color: #808695;
            margin-top: 5px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .radio-item input[type="radio"] {
            margin-right: 6px;
            cursor: pointer;
        }
        
        .radio-item label {
            cursor: pointer;
            font-size: 14px;
            color: #515a6e;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-info {
            background: #e8f4ff;
            border: 1px solid #91d5ff;
            color: #096dd9;
        }
        
        .alert-warning {
            background: #fffbe6;
            border: 1px solid #ffe58f;
            color: #d48806;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e8eaec;
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2d8cf0;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #2b85e4;
        }
        
        .btn-default {
            background: #fff;
            color: #515a6e;
            border: 1px solid #dcdee2;
        }
        
        .btn-default:hover {
            border-color: #2d8cf0;
            color: #2d8cf0;
        }
        
        .express-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .express-type-item {
            padding: 12px;
            border: 1px solid #dcdee2;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .express-type-item:hover {
            border-color: #2d8cf0;
            background: #f0f8ff;
        }
        
        .express-type-item input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .express-type-item label {
            cursor: pointer;
            font-size: 14px;
        }
        
        .express-type-item .type-code {
            font-size: 12px;
            color: #808695;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= isset($config) ? '编辑快递配置' : '新增快递配置' ?></h1>
            <a href="/admin/express/configs" class="back-btn">← 返回列表</a>
        </div>
        
        <div class="form-container">
            <form id="expressConfigForm">
                <input type="hidden" name="id" value="<?= $config['id'] ?? '' ?>">
                
                <!-- 基本信息 -->
                <div class="form-section">
                    <div class="section-title">基本信息</div>
                    
                    <div class="alert alert-info">
                        <strong>提示：</strong>配置顺丰月结账户后，系统将自动使用月结付款方式，享受更优惠的价格。
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                快递公司 <span class="required">*</span>
                            </label>
                            <select name="express_code" class="form-select" required <?= isset($config) ? 'disabled' : '' ?>>
                                <option value="">请选择</option>
                                <option value="SF" <?= isset($config) && $config['express_code'] == 'SF' ? 'selected' : '' ?>>顺丰速运</option>
                                <option value="YTO">圆通速递</option>
                                <option value="ZTO">中通快递</option>
                                <option value="STO">申通快递</option>
                                <option value="YD">韵达快递</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                快递公司名称 <span class="required">*</span>
                            </label>
                            <input type="text" name="express_name" class="form-input" 
                                   value="<?= $config['express_name'] ?? '顺丰速运' ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                合作伙伴ID/客户编码 <span class="required">*</span>
                            </label>
                            <input type="text" name="partner_id" class="form-input" 
                                   value="<?= $config['partner_id'] ?? '' ?>" 
                                   placeholder="例如：SF12345678" required>
                            <div class="form-help">从顺丰开放平台获取</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                校验码/密钥 <span class="required">*</span>
                            </label>
                            <input type="text" name="checkword" class="form-input" 
                                   value="<?= $config['checkword'] ?? '' ?>" 
                                   placeholder="例如：abc123def456" required>
                            <div class="form-help">API密钥，从顺丰开放平台获取</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                月结账号 <span class="required">*</span>
                            </label>
                            <input type="text" name="monthly_account" class="form-input" 
                                   value="<?= $config['monthly_account'] ?? '' ?>" 
                                   placeholder="例如：123456789">
                            <div class="form-help" style="color: #ed4014;">
                                <strong>月结客户必填</strong>，使用月结付款方式，享受更优惠价格
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">排序</label>
                            <input type="number" name="sort_order" class="form-input" 
                                   value="<?= $config['sort_order'] ?? 0 ?>" min="0">
                            <div class="form-help">数值越小越靠前</div>
                        </div>
                    </div>
                </div>
                
                <!-- 发件人信息 -->
                <div class="form-section">
                    <div class="section-title">发件人信息</div>
                    
                    <div class="alert alert-warning">
                        <strong>注意：</strong>月结账号绑定的发货地址必须与此处填写的地址一致，否则可能导致下单失败。
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                发件人姓名 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_name" class="form-input" 
                                   value="<?= $config['sender_name'] ?? '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                发件人手机 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_mobile" class="form-input" 
                                   value="<?= $config['sender_mobile'] ?? '' ?>" 
                                   pattern="^1[3-9]\d{9}$" 
                                   placeholder="13800138000" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                发件省份 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_province" class="form-input" 
                                   value="<?= $config['sender_province'] ?? '' ?>" 
                                   placeholder="广东省" required>
                            <div class="form-help">必须填写完整名称，如"广东省"</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                发件城市 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_city" class="form-input" 
                                   value="<?= $config['sender_city'] ?? '' ?>" 
                                   placeholder="深圳市" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                发件区县 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_county" class="form-input" 
                                   value="<?= $config['sender_county'] ?? '' ?>" 
                                   placeholder="南山区" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full">
                            <label class="form-label">
                                发件详细地址 <span class="required">*</span>
                            </label>
                            <input type="text" name="sender_address" class="form-input" 
                                   value="<?= $config['sender_address'] ?? '' ?>" 
                                   placeholder="科技园南区XXX号" required>
                        </div>
                    </div>
                </div>
                
                <!-- 推荐快递类型 -->
                <div class="form-section">
                    <div class="section-title">推荐快递类型（月结客户专享）</div>
                    
                    <div class="alert alert-info">
                        选择您常用的快递类型，系统会在打单时优先推荐这些类型。
                    </div>
                    
                    <div class="express-type-selector">
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="25" id="type_25" checked>
                            <label for="type_25">
                                顺丰微小件 
                                <span class="type-code">(≤2kg)</span>
                            </label>
                        </div>
                        
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="26" id="type_26" checked>
                            <label for="type_26">
                                填仓标快 
                                <span class="type-code">(非紧急)</span>
                            </label>
                        </div>
                        
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="1" id="type_1">
                            <label for="type_1">
                                标准快递 
                                <span class="type-code">(常规)</span>
                            </label>
                        </div>
                        
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="2" id="type_2">
                            <label for="type_2">
                                顺丰特惠 
                                <span class="type-code">(经济)</span>
                            </label>
                        </div>
                        
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="5" id="type_5">
                            <label for="type_5">
                                顺丰次晨 
                                <span class="type-code">(次日达)</span>
                            </label>
                        </div>
                        
                        <div class="express-type-item">
                            <input type="checkbox" name="express_types[]" value="6" id="type_6">
                            <label for="type_6">
                                顺丰即日 
                                <span class="type-code">(当日达)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- 其他设置 -->
                <div class="form-section">
                    <div class="section-title">其他设置</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">环境模式</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="sandbox_mode" value="1" id="sandbox_1" 
                                           <?= !isset($config) || $config['sandbox_mode'] == 1 ? 'checked' : '' ?>>
                                    <label for="sandbox_1">测试环境</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="sandbox_mode" value="0" id="sandbox_0"
                                           <?= isset($config) && $config['sandbox_mode'] == 0 ? 'checked' : '' ?>>
                                    <label for="sandbox_0">生产环境</label>
                                </div>
                            </div>
                            <div class="form-help">测试环境用于调试，生产环境用于正式下单</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">状态</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="status" value="1" id="status_1" 
                                           <?= !isset($config) || $config['status'] == 1 ? 'checked' : '' ?>>
                                    <label for="status_1">启用</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="status" value="0" id="status_0"
                                           <?= isset($config) && $config['status'] == 0 ? 'checked' : '' ?>>
                                    <label for="status_0">禁用</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full">
                            <label class="form-label">备注</label>
                            <textarea name="remark" class="form-textarea"><?= $config['remark'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- 提交按钮 -->
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">保存配置</button>
                    <button type="button" class="btn btn-default" onclick="window.location.href='/admin/express/configs'">取消</button>
                    <button type="button" class="btn btn-default" onclick="testConnection()">测试连接</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // 表单提交
        document.getElementById('expressConfigForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // 收集选中的快递类型
            const expressTypes = [];
            document.querySelectorAll('input[name="express_types[]"]:checked').forEach(cb => {
                expressTypes.push(cb.value);
            });
            data.express_types = expressTypes.join(',');
            
            try {
                const response = await fetch('/admin/express/configs/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.code === 0) {
                    alert('保存成功！');
                    window.location.href = '/admin/express/configs';
                } else {
                    alert('保存失败：' + result.msg);
                }
            } catch (error) {
                alert('保存失败：' + error.message);
            }
        });
        
        // 测试连接
        async function testConnection() {
            const formData = new FormData(document.getElementById('expressConfigForm'));
            const data = Object.fromEntries(formData.entries());
            
            if (!data.partner_id || !data.checkword) {
                alert('请先填写合作伙伴ID和校验码');
                return;
            }
            
            try {
                const response = await fetch('/admin/express/configs/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.code === 0) {
                    alert('连接测试成功！');
                } else {
                    alert('连接测试失败：' + result.msg);
                }
            } catch (error) {
                alert('测试失败：' + error.message);
            }
        }
        
        // 快递公司切换时自动填充名称
        document.querySelector('select[name="express_code"]').addEventListener('change', function() {
            const names = {
                'SF': '顺丰速运',
                'YTO': '圆通速递',
                'ZTO': '中通快递',
                'STO': '申通快递',
                'YD': '韵达快递'
            };
            
            const nameInput = document.querySelector('input[name="express_name"]');
            if (names[this.value]) {
                nameInput.value = names[this.value];
            }
        });
    </script>
</body>
</html>
