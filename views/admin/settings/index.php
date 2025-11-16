<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站设置 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .settings-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .settings-nav {
            border-right: 1px solid #e0e0e0;
            padding: 20px 0;
        }
        .settings-nav .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .settings-nav .nav-link:hover {
            background: #f5f5f5;
            border-left-color: #007bff;
        }
        .settings-nav .nav-link.active {
            background: #e3f2fd;
            border-left-color: #007bff;
            color: #007bff;
            font-weight: bold;
        }
        .settings-content {
            padding: 30px;
        }
        .setting-group {
            margin-bottom: 30px;
        }
        .setting-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #f0f0f0;
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .setting-description {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        .btn-upload {
            margin-top: 10px;
        }
        .switch-container {
            display: flex;
            align-items: center;
        }
        .custom-switch {
            transform: scale(1.2);
            margin-right: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><i class="fas fa-cog"></i> 网站设置</h2>
                <p class="text-muted">配置网站基本信息和系统参数</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="settings-container">
                    <div class="row no-gutters">
                        <!-- 左侧导航 -->
                        <div class="col-md-2">
                            <div class="settings-nav">
                                <div class="nav flex-column">
                                    <?php foreach ($groups as $key => $name): ?>
                                        <a class="nav-link <?= $currentGroup === $key ? 'active' : '' ?>" 
                                           href="?group=<?= $key ?>">
                                            <i class="fas fa-<?= $this->getGroupIcon($key) ?>"></i> 
                                            <?= $name ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 右侧内容 -->
                        <div class="col-md-10">
                            <div class="settings-content">
                                <form id="settingsForm">
                                    <div class="setting-group">
                                        <h4 class="mb-4">
                                            <i class="fas fa-<?= $this->getGroupIcon($currentGroup) ?>"></i>
                                            <?= $groups[$currentGroup] ?>
                                        </h4>

                                        <?php if (empty($settings)): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> 该分组暂无配置项
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($settings as $setting): ?>
                                                <div class="setting-item">
                                                    <div class="setting-label">
                                                        <?= htmlspecialchars($setting['label']) ?>
                                                    </div>

                                                    <?php if ($setting['type'] === 'text'): ?>
                                                        <input type="text" 
                                                               class="form-control" 
                                                               name="<?= $setting['key'] ?>" 
                                                               value="<?= htmlspecialchars($setting['value']) ?>">

                                                    <?php elseif ($setting['type'] === 'textarea'): ?>
                                                        <textarea class="form-control" 
                                                                  name="<?= $setting['key'] ?>" 
                                                                  rows="4"><?= htmlspecialchars($setting['value']) ?></textarea>

                                                    <?php elseif ($setting['type'] === 'number'): ?>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               name="<?= $setting['key'] ?>" 
                                                               value="<?= htmlspecialchars($setting['value']) ?>">

                                                    <?php elseif ($setting['type'] === 'switch'): ?>
                                                        <div class="switch-container">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input" 
                                                                       id="switch_<?= $setting['key'] ?>"
                                                                       name="<?= $setting['key'] ?>"
                                                                       value="1"
                                                                       <?= $setting['value'] == '1' ? 'checked' : '' ?>>
                                                                <label class="custom-control-label" 
                                                                       for="switch_<?= $setting['key'] ?>">
                                                                    <?= $setting['value'] == '1' ? '已启用' : '已禁用' ?>
                                                                </label>
                                                            </div>
                                                        </div>

                                                    <?php elseif ($setting['type'] === 'image'): ?>
                                                        <div>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="<?= $setting['key'] ?>" 
                                                                   id="input_<?= $setting['key'] ?>"
                                                                   value="<?= htmlspecialchars($setting['value']) ?>"
                                                                   placeholder="图片URL">
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-primary btn-upload"
                                                                    onclick="uploadImage('<?= $setting['key'] ?>')">
                                                                <i class="fas fa-upload"></i> 上传图片
                                                            </button>

                                                            <?php if (!empty($setting['value'])): ?>
                                                                <div>
                                                                    <img src="<?= htmlspecialchars($setting['value']) ?>" 
                                                                         class="image-preview"
                                                                         id="preview_<?= $setting['key'] ?>"
                                                                         alt="预览">
                                                                </div>
                                                            <?php endif; ?>

                                                            <input type="file" 
                                                                   id="file_<?= $setting['key'] ?>" 
                                                                   style="display:none" 
                                                                   accept="image/*">
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($setting['description'])): ?>
                                                        <div class="setting-description">
                                                            <i class="fas fa-info-circle"></i>
                                                            <?= htmlspecialchars($setting['description']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> 保存设置
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-lg" onclick="location.reload()">
                                            <i class="fas fa-redo"></i> 重置
                                        </button>
                                        <a href="/admin/settings/create" class="btn btn-success btn-lg">
                                            <i class="fas fa-plus"></i> 添加配置项
                                        </a>
                                        <button type="button" class="btn btn-info btn-lg" onclick="exportSettings()">
                                            <i class="fas fa-download"></i> 导出配置
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // 表单提交
        $('#settingsForm').on('submit', function(e) {
            e.preventDefault();
            
            // 处理switch开关（未选中的不会提交，需要手动设置为0）
            $('input[type="checkbox"]').each(function() {
                if (!$(this).is(':checked')) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: $(this).attr('name'),
                        value: '0'
                    }).appendTo('#settingsForm');
                }
            });

            $.ajax({
                url: '/admin/settings/update',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.code === 200) {
                        alert('保存成功！');
                        location.reload();
                    } else {
                        alert(res.message || '保存失败');
                    }
                },
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        });

        // 上传图片
        function uploadImage(key) {
            $('#file_' + key).click();
            
            $('#file_' + key).off('change').on('change', function() {
                const file = this.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                $.ajax({
                    url: '/admin/settings/upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.code === 200) {
                            $('#input_' + key).val(res.data.url);
                            
                            // 显示预览
                            if ($('#preview_' + key).length) {
                                $('#preview_' + key).attr('src', res.data.url);
                            } else {
                                $('<img>').attr({
                                    src: res.data.url,
                                    class: 'image-preview',
                                    id: 'preview_' + key
                                }).appendTo('#input_' + key).parent();
                            }

                            alert('上传成功！');
                        } else {
                            alert(res.message || '上传失败');
                        }
                    },
                    error: function() {
                        alert('上传失败，请稍后重试');
                    }
                });
            });
        }

        // 导出配置
        function exportSettings() {
            window.location.href = '/admin/settings/export';
        }

        // Switch开关显示文字切换
        $('input[type="checkbox"]').on('change', function() {
            const label = $(this).next('label');
            if ($(this).is(':checked')) {
                label.text('已启用');
            } else {
                label.text('已禁用');
            }
        });
    </script>
</body>
</html>

<?php
// 辅助函数：获取分组图标
function getGroupIcon($group) {
    $icons = [
        'basic' => 'home',
        'contact' => 'phone',
        'mall' => 'shopping-cart',
        'order' => 'file-alt',
        'upload' => 'cloud-upload-alt',
        'social' => 'share-alt',
        'miniprogram' => 'mobile-alt',
        'other' => 'cogs',
    ];
    return $icons[$group] ?? 'cog';
}

// 将函数添加到视图上下文
$this->getGroupIcon = 'getGroupIcon';
?>
