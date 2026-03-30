document.addEventListener('DOMContentLoaded', function() {
    // ========== ЭЛЕМЕНТЫ DOM ==========
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const backToRoleBtn = document.getElementById('backToRoleBtn');
    const roleButtons = document.querySelectorAll('.role-btn');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const applicantProfileForm = document.getElementById('applicantProfileForm');
    const employerProfileForm = document.getElementById('employerProfileForm');
    const userForms = document.getElementById('userForms');
    const curatorForm = document.getElementById('curatorForm');
    
    const submitLogin = document.getElementById('submitLogin');
    const nextToProfileBtn = document.getElementById('nextToProfileBtn');
    const completeApplicantRegistration = document.getElementById('completeApplicantRegistration');
    const completeEmployerRegistration = document.getElementById('completeEmployerRegistration');
    const completeEmployerRegistrationEgrul = document.getElementById('completeEmployerRegistrationEgrul');
    const submitCuratorLogin = document.getElementById('submitCuratorLogin');
    
    const backToRegisterFromApplicant = document.getElementById('backToRegisterFromApplicant');
    const backToRegisterFromEmployer = document.getElementById('backToRegisterFromEmployer');
    
    const registerModeRadio = document.getElementById('register-mode');
    const loginModeRadio = document.getElementById('login-mode');
    const innModeRadio = document.getElementById('inn-mode');
    const egrulModeRadio = document.getElementById('egrul-mode');
    const innForm = document.getElementById('innForm');
    const egrulForm = document.getElementById('egrulForm');
    
    let selectedRole = null;
    let registrationData = {};

        // ========== ЧТЕНИЕ ПАРАМЕТРОВ ИЗ URL ==========
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    const preselectedRole = getUrlParameter('role');
    
    if (preselectedRole && (preselectedRole === 'applicant' || preselectedRole === 'employer' || preselectedRole === 'curator')) {
        // Откладываем выполнение, чтобы DOM загрузился
        setTimeout(() => {
            // Находим и кликаем на кнопку роли
            const roleButton = document.querySelector(`.role-btn[data-role="${preselectedRole}"]`);
            if (roleButton) {
                roleButton.click();
                
                // Автоматически переходим ко второму шагу
                const nextBtn = document.getElementById('nextStepBtn');
                if (nextBtn) {
                    setTimeout(() => {
                        nextBtn.click();
                    }, 150);
                }
            }
            
            // Если куратор — показываем форму входа сразу
            if (preselectedRole === 'curator') {
                setTimeout(() => {
                    const curatorForm = document.getElementById('curatorForm');
                    const userForms = document.getElementById('userForms');
                    if (curatorForm && userForms) {
                        userForms.style.display = 'none';
                        curatorForm.style.display = 'block';
                    }
                }, 300);
            }
        }, 100);
    }
    
    // ========== ФУНКЦИЯ ОТПРАВКИ НА СЕРВЕР ==========
    async function sendToServer(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('Ошибка:', error);
            return { success: false, message: 'Ошибка соединения с сервером' };
        }
    }
    
    // ========== ФУНКЦИЯ ПРОВЕРКИ EMAIL ==========
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // ========== ФУНКЦИЯ УВЕДОМЛЕНИЙ ==========
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: ${type === 'success' ? '#9A84FF' : '#ff4444'};
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            z-index: 10000;
            font-family: "Inter", sans-serif;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
    
    // ========== 1. ВЫБОР РОЛИ ==========
    roleButtons.forEach(button => {
        button.addEventListener('click', function() {
            roleButtons.forEach(btn => btn.classList.remove('selected'));
            this.classList.add('selected');
            selectedRole = this.getAttribute('data-role');
            console.log('Выбрана роль:', selectedRole);
        });
    });
    
    // ========== 2. ПЕРЕХОД КО ВТОРОМУ ШАГУ ==========
    nextStepBtn.addEventListener('click', function() {
        if (!selectedRole) {
            showNotification('Пожалуйста, выберите роль', 'error');
            return;
        }
        step1.style.display = 'none';
        step2.style.display = 'block';
        
        if (selectedRole === 'curator') {
            userForms.style.display = 'none';
            curatorForm.style.display = 'block';
            applicantProfileForm.style.display = 'none';
            employerProfileForm.style.display = 'none';
        } else {
            userForms.style.display = 'block';
            curatorForm.style.display = 'none';
            applicantProfileForm.style.display = 'none';
            employerProfileForm.style.display = 'none';
        }
    });
    
    // ========== 3. ДАЛЕЕ (ПОСЛЕ ВВОДА EMAIL/ПАРОЛЯ) ==========
    nextToProfileBtn.addEventListener('click', function() {
        const email = document.getElementById('regEmail').value;
        const name = document.getElementById('regName').value;
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;
        
        if (!email || !name || !password || !confirmPassword) {
            showNotification('Пожалуйста, заполните все поля', 'error');
            return;
        }
        if (password !== confirmPassword) {
            showNotification('Пароли не совпадают', 'error');
            return;
        }
        if (password.length < 6) {
            showNotification('Пароль должен содержать минимум 6 символов', 'error');
            return;
        }
        if (!isValidEmail(email)) {
            showNotification('Введите корректный email', 'error');
            return;
        }
        
        registrationData = { email, name, password };
        
        if (selectedRole === 'applicant') {
            userForms.style.display = 'none';
            applicantProfileForm.style.display = 'block';
        } else if (selectedRole === 'employer') {
            userForms.style.display = 'none';
            employerProfileForm.style.display = 'block';
        }
    });
    
    // ========== 4. РЕГИСТРАЦИЯ СОИСКАТЕЛЯ ==========
    completeApplicantRegistration.addEventListener('click', async function() {
        console.log('🔵 Кнопка "Завершить регистрацию" нажата');
        
        const lastName = document.getElementById('applicantLastName').value;
        const firstName = document.getElementById('applicantFirstName').value;
        const patronymic = document.getElementById('applicantPatronymic').value;
        const university = document.getElementById('applicantUniversity').value;
        const graduationYear = document.getElementById('applicantGraduationYear').value;
        const studyingNow = document.getElementById('applicantStudyingNow').checked;
        
        if (!lastName || !firstName || !university) {
            showNotification('Заполните обязательные поля (Фамилия, Имя, Учебное заведение)', 'error');
            return;
        }
        
        // Собираем навыки
        const skills = [];
        document.querySelectorAll('.checkbox-container input[type="checkbox"]').forEach(cb => {
            if (cb.checked && cb.id !== 'applicantStudyingNow') {
                const label = cb.closest('.checkbox-label');
                const skillText = label?.querySelector('.checkbox-text')?.innerText;
                if (skillText) skills.push(skillText);
            }
        });
        
        const fullData = {
            ...registrationData,
            lastName: lastName,
            firstName: firstName,
            patronymic: patronymic,
            university: university,
            graduationYear: graduationYear,
            studyingNow: studyingNow,
            skills: skills,
            role: 'applicant'
        };
        
        console.log('📤 Отправляем данные на сервер:', fullData);
        
        const result = await sendToServer('api/register.php', fullData);
        console.log('📥 Ответ сервера:', result);
        
        if (result.success) {
            localStorage.setItem('userId', result.userId);
            localStorage.setItem('userRole', result.role);
            localStorage.setItem('userName', result.name);
            showNotification('✅ Регистрация успешна!', 'success');
            setTimeout(() => {
                window.location.href = 'applicant-dashboard.html';
            }, 1500);
        } else {
            showNotification('❌ ' + result.message, 'error');
        }
    });
    
    // ========== 5. РЕГИСТРАЦИЯ РАБОТОДАТЕЛЯ (ИНН + САЙТ) ==========
    completeEmployerRegistration.addEventListener('click', async function() {
        const inn = document.getElementById('employerInn').value;
        const website = document.getElementById('employerWebsite').value;
        
        if (!inn || !website) {
            showNotification('Пожалуйста, заполните все поля', 'error');
            return;
        }
        
        const fullData = {
            ...registrationData,
            verificationType: 'inn',
            inn: inn,
            website: website,
            role: 'employer'
        };
        
        const result = await sendToServer('api/register.php', fullData);
        
        if (result.success) {
            localStorage.setItem('userId', result.userId);
            localStorage.setItem('userRole', result.role);
            localStorage.setItem('userName', result.name);
            showNotification('Регистрация успешна! Данные отправлены на проверку', 'success');
            setTimeout(() => {
                window.location.href = 'employer-dashboard.html';
            }, 1500);
        } else {
            showNotification(result.message, 'error');
        }
    });
    
    // ========== 6. РЕГИСТРАЦИЯ РАБОТОДАТЕЛЯ (ВЫПИСКА ЕГРЮЛ) ==========
    completeEmployerRegistrationEgrul.addEventListener('click', async function() {
        const fileInput = document.getElementById('egrulFile');
        const uploadedFile = fileInput.files[0];
        
        if (!uploadedFile) {
            showNotification('Пожалуйста, загрузите файл', 'error');
            return;
        }
        
        const fullData = {
            ...registrationData,
            verificationType: 'egrul',
            fileName: uploadedFile.name,
            fileSize: uploadedFile.size,
            role: 'employer'
        };
        
        const result = await sendToServer('api/register.php', fullData);
        
        if (result.success) {
            localStorage.setItem('userId', result.userId);
            localStorage.setItem('userRole', result.role);
            localStorage.setItem('userName', result.name);
            showNotification('Регистрация успешна! Документы отправлены на проверку', 'success');
            setTimeout(() => {
                window.location.href = 'employer-dashboard.html';
            }, 1500);
        } else {
            showNotification(result.message, 'error');
        }
    });
    
    // ========== 7. ВХОД ==========
    submitLogin.addEventListener('click', async function() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        if (!email || !password) {
            showNotification('Пожалуйста, заполните все поля', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showNotification('Введите корректный email', 'error');
            return;
        }
        
        const result = await sendToServer('api/login.php', {
            email: email,
            password: password
        });
        
        if (result.success) {
            localStorage.setItem('userId', result.userId);
            localStorage.setItem('userRole', result.role);
            localStorage.setItem('userName', result.name);
            showNotification(`Добро пожаловать, ${result.name}!`, 'success');
            
            setTimeout(() => {
                if (result.role === 'employer') {
                    window.location.href = 'employer-dashboard.html';
                } else if (result.role === 'curator') {
                    window.location.href = 'curator-dashboard.html';
                } else {
                    window.location.href = 'applicant-dashboard.html';
                }
            }, 1500);
        } else {
            showNotification(result.message, 'error');
        }
    });
    
    // ========== 8. ВХОД КУРАТОРА ==========
    submitCuratorLogin.addEventListener('click', async function() {
        const email = document.getElementById('curatorEmail').value;
        const password = document.getElementById('curatorPassword').value;
        
        if (!email || !password) {
            showNotification('Пожалуйста, заполните все поля', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showNotification('Введите корректный email', 'error');
            return;
        }
        
        const result = await sendToServer('api/login.php', {
            email: email,
            password: password,
            role: 'curator'  // явно указываем роль куратора
        });
        
        if (result.success && result.role === 'curator') {
            localStorage.setItem('userId', result.userId);
            localStorage.setItem('curatorId', result.curatorId);
            localStorage.setItem('userRole', result.role);
            localStorage.setItem('userName', result.name);
            localStorage.setItem('curatorEmail', result.email);
            localStorage.setItem('institution', result.institution || '');
            
            showNotification(`Добро пожаловать, куратор ${result.name}!`, 'success');
            
            setTimeout(() => {
                window.location.href = 'curator-dashboard.html';
            }, 1500);
        } else {
            showNotification(result.message || 'Неверный email или пароль', 'error');
        }
    });
    
    // ========== 9. ПЕРЕКЛЮЧЕНИЕ МЕЖДУ ВХОДОМ И РЕГИСТРАЦИЕЙ ==========
    function switchAuthMode(mode) {
        if (mode === 'register') {
            registerForm.style.display = 'flex';
            loginForm.style.display = 'none';
        } else {
            registerForm.style.display = 'none';
            loginForm.style.display = 'flex';
        }
    }
    
    if (registerModeRadio && loginModeRadio) {
        registerModeRadio.addEventListener('change', () => switchAuthMode('register'));
        loginModeRadio.addEventListener('change', () => switchAuthMode('login'));
    }
    
    // ========== 10. ПЕРЕКЛЮЧЕНИЕ СПОСОБОВ ВЕРИФИКАЦИИ ==========
    if (innModeRadio && egrulModeRadio) {
        innModeRadio.addEventListener('change', function() {
            if (this.checked) {
                innForm.style.display = 'flex';
                egrulForm.style.display = 'none';
            }
        });
        egrulModeRadio.addEventListener('change', function() {
            if (this.checked) {
                innForm.style.display = 'none';
                egrulForm.style.display = 'flex';
            }
        });
    }
    
    // ========== 11. ЗАГРУЗКА ФАЙЛА ДЛЯ ЕГРЮЛ ==========
    const fileInput = document.getElementById('egrulFile');
    const uploadedFileInfo = document.getElementById('uploadedFileInfo');
    const fileNameSpan = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFileBtn');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0 && fileNameSpan) {
                fileNameSpan.textContent = this.files[0].name;
                if (uploadedFileInfo) uploadedFileInfo.style.display = 'flex';
            }
        });
    }
    
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', function() {
            fileInput.value = '';
            if (uploadedFileInfo) uploadedFileInfo.style.display = 'none';
        });
    }
    
    // ========== 12. КНОПКИ НАЗАД ==========
    backToRoleBtn.addEventListener('click', function() {
        step2.style.display = 'none';
        step1.style.display = 'block';
    });
    
    if (backToRegisterFromApplicant) {
        backToRegisterFromApplicant.addEventListener('click', function() {
            applicantProfileForm.style.display = 'none';
            userForms.style.display = 'block';
            registerForm.style.display = 'flex';
            loginForm.style.display = 'none';
            if (registerModeRadio) registerModeRadio.checked = true;
        });
    }
    
    if (backToRegisterFromEmployer) {
        backToRegisterFromEmployer.addEventListener('click', function() {
            employerProfileForm.style.display = 'none';
            userForms.style.display = 'block';
            registerForm.style.display = 'flex';
            loginForm.style.display = 'none';
            if (registerModeRadio) registerModeRadio.checked = true;
        });
    }
});