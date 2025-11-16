<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加配置项 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>
                            <a href="/admin/settings" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> 返回
                            </a>
                            添加配置项
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="createForm">
                            <div class="form-group">
                                <label>配置键 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="key" required
                                       placeholder="例如: custom_setting_key">
                                <small class="form-text text-muted">唯一标识，建议使用英文和下划线</small>
                            </div>

                            <div class="form-group">
                                <label>配置标签 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="label" required
                                       placeholder="例如: 自定义设置">
                            </div>

                            <div class="form-group">
                                <label>配置值</label>
                                <input type="text" class="form-control" name="value"
                                       placeholder="默认值">
                            </div>

                            <div class="form-group">
                                <label>值类型 <span class="text-danger">*</span></label>
                                <select class="form-control" name="type" required>
                                    <option value="text">文本框</option>
                                    <option value="textarea">多行文本</option>
                                    <option value="number">数字</option>
                                    <option value="image">图片</option>
                                    <option value="switch">开关</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>配置分组</label>
                                <select class="form-control" name="group">
                                    <option value="basic">基本信息</option>
                                    <option value="contact">联系方式</option>
                                    <option value="mall">商城设置</option>
                                    <option value="order">订单设置</option>
                                    <option value="upload">上传设置</option>
                                    <option value="social">社交媒体</option>
                                    <option value="other" selected>其他设置</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>配置说明</label>
                                <textarea class="form-control" name="description" rows="3"
                                          placeholder="简要说明此配置的作用"></textarea>
                            </div>

                            <div class="form-group">
                                <label>排序</label>
                                <input type="number" class="form-control" name="sort" value="0">
                                <small class="form-text text-muted">数字越小越靠前</small>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> 保存
                                </button>
                                <a href="/admin/settings" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> 取消
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#createForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '/admin/settings/store',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.code === 200) {
                        alert('创建成功！');
                        window.location.href = '/admin/settings';
                    } else {
                        alert(res.message || '创建失败');
                    }
                },
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        });
    </script>
</body>
</html>
