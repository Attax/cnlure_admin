<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录页面</title>
    <!-- 引入Tailwind CSS -->
    <script src="{{ asset('js/tailwind.3.4.17.min.js') }}"></script>
    <!-- 引入字体图标 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .input-field:focus {
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2);
        }

        .btn-login {
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-900 to-black min-h-screen flex items-center justify-center p-4">
    <div class="login-card bg-white rounded-2xl p-8 w-full max-w-md">
        <!-- 标题部分 -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">登录账户</h1>
            <p class="text-gray-600">请输入您的用户名和密码</p>
        </div>

        <!-- 登录表单 -->
        <form id="J-LoginForm" class="space-y-6">
            <!-- 用户名输入框 -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">用户名</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="input-field w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-gray-500 transition-colors"
                        placeholder="请输入用户名"
                        required>
                </div>
            </div>

            <!-- 密码输入框 -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">密码</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input-field w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-gray-500 transition-colors"
                        placeholder="请输入密码"
                        required>
                </div>
            </div>

            <!-- 记住我和忘记密码 -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">记住我</label>
                </div>
                <div>
                    <a href="#" class="text-sm font-medium text-gray-700 hover:text-black transition-colors">忘记密码?</a>
                </div>
            </div>

            <!-- 登录按钮 -->
            <button
                type="submit"
                class="btn-login w-full bg-black text-white py-3 px-4 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                登录
            </button>
        </form>

        <!-- 注册链接 -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                还没有账户?
                <a href="#" class="font-medium text-gray-900 hover:text-black transition-colors">立即注册</a>
            </p>
        </div>

        <!-- 分隔线 -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">或使用以下方式登录</span>
                </div>
            </div>

            <!-- 社交登录按钮 -->
            <div class="mt-4 grid grid-cols-2 gap-3">
                <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fab fa-google text-red-500 mr-2"></i> Google
                </button>
                <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fab fa-github text-gray-900 mr-2"></i> GitHub
                </button>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>
    <script>
        $(function() {
            // 初始化表单验证
            $('#J-LoginForm').validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 3,
                        maxlength: 20,
                    },
                    password: {
                        required: true,
                        minlength: 6,
                        maxlength: 20,
                    },
                },
                messages: {
                    username: {
                        required: '请输入用户名',
                        minlength: '用户名长度不能小于3个字符',
                        maxlength: '用户名长度不能大于20个字符',
                    },
                    password: {
                        required: '请输入密码',
                        minlength: '密码长度不能小于6个字符',
                        maxlength: '密码长度不能大于20个字符',
                    },
                },
                submitHandler: function(form) {
                    form.submit();
                },
            });
        })
    </script>
</body>

</html>