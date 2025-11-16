<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action == 'create' ? '添加' : '编辑' ?>支付通道 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }
        .form-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>
                    <a href="/admin/payment/channels" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> 返回
                    </a>
                    <?= $action == 'create' ? '添加' : '编辑' ?>支付通道
                </h2>
                <hr>
            </div>
        </div>

        <form id="channelForm">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id" value="<?= $channel['id'] ?>">
            <?php endif; ?>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> 基本信息
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>通道名称 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= $channel['name'] ?? '' ?>" required>
                        <small class="form-help">例如: 微信支付主通道</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label>支付类型 <span class="text-danger">*</span></label>
                        <select class="form-control" name="type" required>
                            <option value="wechat" <?= ($channel['type'] ?? 'wechat') == 'wechat' ? 'selected' : '' ?>>
                                微信支付
                            </option>
                            <option value="alipay" <?= ($channel['type'] ?? '') == 'alipay' ? 'selected' : '' ?>>
                                支付宝
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>应用ID (APPID) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="app_id" 
                               value="<?= $channel['app_id'] ?? '' ?>" required>
                        <small class="form-help" id="app_id_help">微信公众平台/开放平台的APPID</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label>商户号 (MCH_ID) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="mch_id" 
                               value="<?= $channel['mch_id'] ?? '' ?>" id="mch_id_field">
                        <small class="form-help" id="mch_id_help">微信支付商户号</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>API密钥/私钥 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="api_key" 
                           value="<?= $channel['api_key'] ?? '' ?>" required id="api_key_field">
                    <small class="form-help" id="api_key_help">32位的APIv3密钥，在商户平台设置</small>
                </div>

                <!-- 支付宝特定字段 -->
                <div id="alipay_fields" style="display: none;">
                    <div class="form-group">
                        <label>商户私钥 <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alipay_private_key" rows="5" 
                                  placeholder="请输入支付宝商户私钥内容"><?= isset($channel['config']) && ($config = json_decode($channel['config'], true)) ? ($config['private_key'] ?? '') : '' ?></textarea>
                        <small class="form-help">支付宝商户私钥，用于生成签名</small>
                    </div>

                    <div class="form-group">
                        <label>支付宝公钥 <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alipay_public_key" rows="5" 
                                  placeholder="请输入支付宝公钥内容"><?= isset($channel['config']) && ($config = json_decode($channel['config'], true)) ? ($config['public_key'] ?? '') : '' ?></textarea>
                        <small class="form-help">支付宝公钥，用于验证签名</small>
                    </div>

                    <div class="form-group">
                        <label>返回地址 (Return URL)</label>
                        <input type="text" class="form-control" name="alipay_return_url" 
                               value="<?= isset($channel['config']) && ($config = json_decode($channel['config'], true)) ? ($config['return_url'] ?? '') : '' ?>"
                               placeholder="支付宝支付完成后跳转的地址">
                        <small class="form-help">支付完成后用户返回的页面地址</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-certificate"></i> 证书配置
                </div>

                <!-- 微信支付特定字段 -->
                <div id="wechat_fields">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>商户证书路径</label>
                            <input type="text" class="form-control" name="cert_path" 
                                   value="<?= $channel['cert_path'] ?? '' ?>" 
                                   placeholder="/certs/apiclient_cert.pem">
                            <small class="form-help">相对于项目根目录的路径</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label>商户私钥路径</label>
                            <input type="text" class="form-control" name="key_path" 
                                   value="<?= $channel['key_path'] ?? '' ?>"
                                   placeholder="/certs/apiclient_key.pem">
                            <small class="form-help">相对于项目根目录的路径</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>回调地址 (Notify URL)</label>
                        <input type="text" class="form-control" name="notify_url" 
                               value="<?= $channel['notify_url'] ?? '' ?>"
                               placeholder="留空则使用默认: <?= config('app.url') ?>/payment/wechat/notify">
                        <small class="form-help">支付成功后的异步通知地址，留空使用默认</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-cog"></i> 其他设置
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" 
                                   name="is_active" <?= ($channel['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="is_active">启用此通道</label>
                        </div>
                        <small class="form-help">只有启用的通道才能用于支付</small>
                    </div>

                    <div class="form-group col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_default" 
                                   name="is_default" <?= ($channel['is_default'] ?? 0) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="is_default">设为默认通道</label>
                        </div>
                        <small class="form-help">未指定通道时将使用默认通道</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>备注说明</label>
                    <textarea class="form-control" name="remark" rows="3"><?= $channel['remark'] ?? '' ?></textarea>
                    <small class="form-help">此通道的用途说明或其他备注信息</small>
                </div>
            </div>

            <div class="form-section">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> 保存配置
                </button>
                <a href="/admin/payment/channels" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> 取消
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // 支付类型切换处理
        function togglePaymentFields() {
            const paymentType = $('select[name="type"]').val();
            
            if (paymentType === 'alipay') {
                // 显示支付宝字段
                $('#alipay_fields').show();
                $('#wechat_fields').hide();
                
                // 更新标签和帮助文本
                $('#app_id_help').text('支付宝开放平台的APPID');
                $('#mch_id_field').prop('required', false);
                $('#mch_id_help').html('支付宝商户号 (<span class="text-muted">可选</span>)');
                $('#api_key_field').prop('required', true);
                $('#api_key_help').text('支付宝应用私钥内容（用于生成签名）');
                
                // 隐藏商户号字段的必填标识
                $('label[for="mch_id"] span.text-danger').hide();
            } else {
                // 显示微信支付字段
                $('#alipay_fields').hide();
                $('#wechat_fields').show();
                
                // 恢复标签和帮助文本
                $('#app_id_help').text('微信公众平台/开放平台的APPID');
                $('#mch_id_field').prop('required', true);
                $('#mch_id_help').text('微信支付商户号');
                $('#api_key_field').prop('required', true);
                $('#api_key_help').text('32位的APIv3密钥，在商户平台设置');
                
                // 显示商户号字段的必填标识
                $('label[for="mch_id"] span.text-danger').show();
            }
        }
        
        // 表单提交处理
        $('#channelForm').on('submit', function(e) {
            e.preventDefault();
            
            const paymentType = $('select[name="type"]').val();
            const formData = new FormData();
            
            // 收集表单数据
            $(this).serializeArray().forEach(function(field) {
                formData.append(field.name, field.value);
            });
            
            // 如果是支付宝支付，需要处理配置字段
            if (paymentType === 'alipay') {
                const config = {
                    private_key: $('textarea[name="alipay_private_key"]').val(),
                    public_key: $('textarea[name="alipay_public_key"]').val(),
                    return_url: $('input[name="alipay_return_url"]').val()
                };
                formData.append('config', JSON.stringify(config));
            }
            
            // 移除支付宝特定的字段，避免重复提交
            formData.delete('alipay_private_key');
            formData.delete('alipay_public_key');
            formData.delete('alipay_return_url');
            
            const action = '<?= $action ?>';
            const url = action === 'create' 
                ? '/admin/payment/channels/store' 
                : '/admin/payment/channels/update';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.code === 200) {
                        alert('保存成功！');
                        window.location.href = '/admin/payment/channels';
                    } else {
                        alert(res.message || '保存失败');
                    }
                },
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        });
        
        // 初始化页面
        $(document).ready(function() {
            // 绑定支付类型切换事件
            $('select[name="type"]').on('change', togglePaymentFields);
            
            // 初始化字段显示
            togglePaymentFields();
        });
    </script>
</body>
</html>
