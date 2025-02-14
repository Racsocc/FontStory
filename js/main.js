document.addEventListener('DOMContentLoaded', () => {
    // 文本输入处理
    const textInput = document.querySelector('.text-input');
    const fontSamples = document.querySelectorAll('.font-sample');

    textInput?.addEventListener('input', (e) => {
        const newText = e.target.value || textInput.placeholder;
        fontSamples.forEach(sample => {
            sample.textContent = newText;
        });
    });

    // 字体大小切换
    const sizeButtons = document.querySelectorAll('[data-size]');
    sizeButtons.forEach(button => {
        button.addEventListener('click', () => {
            // 只移除字体大小按钮的激活状态
            sizeButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            button.classList.add('active');

            const size = button.dataset.size;
            document.querySelectorAll('.font-sample').forEach(sample => {
                sample.className = 'font-sample text-' + size;
            });
        });
    });

    // 主题切换
    const themeButtons = document.querySelectorAll('[data-theme]');
    const root = document.documentElement;

    // 设置默认深色主题
    root.setAttribute('data-theme', 'dark');

    // 设置深色按钮为激活状态
    const darkButton = document.querySelector('[data-theme="dark"]');
    if (darkButton) {
        darkButton.classList.add('active');
    }

    // 设置默认字号为"大"
    const defaultSizeBtn = document.querySelector('[data-size="large"]');
    if (defaultSizeBtn) {
        defaultSizeBtn.click();
    }

    // 处理主题切换
    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            themeButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            button.classList.add('active');
            const theme = button.dataset.theme;
            root.setAttribute('data-theme', theme);
        });
    });
}); 