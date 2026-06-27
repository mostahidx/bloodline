document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. AUTH LOGIC ---
    const btnPatient = document.getElementById('btn-patient');
    const btnStaff = document.getElementById('btn-staff');
    const btnAdmin = document.getElementById('btn-admin'); // New Admin Button
    const linkSignup = document.getElementById('btn-signup-toggle');
    const linkLogin = document.getElementById('btn-login-toggle');

    if (btnPatient) btnPatient.addEventListener('click', () => switchAuthType('patient'));
    if (btnStaff) btnStaff.addEventListener('click', () => switchAuthType('staff'));
    if (btnAdmin) btnAdmin.addEventListener('click', () => switchAuthType('admin')); // New Listener
    
    if (linkSignup) linkSignup.addEventListener('click', toggleSignup);
    if (linkLogin) linkLogin.addEventListener('click', toggleSignup);

    function switchAuthType(type) {
        document.getElementById('user_type_input').value = type;
        
        // Reset all buttons
        btnPatient.classList.remove('active');
        btnStaff.classList.remove('active');
        if(btnAdmin) btnAdmin.classList.remove('active');

        // Hide all sections
        document.getElementById('patient-login-inputs').style.display = 'none';
        document.getElementById('staff-login-inputs').style.display = 'none';
        document.getElementById('admin-login-inputs').style.display = 'none';

        // Activate specific section
        if(type === 'patient') {
            btnPatient.classList.add('active');
            document.getElementById('patient-login-inputs').style.display = 'block';
            document.getElementById('login-title').innerText = 'Patient Sign In';
        } else if (type === 'staff') {
            btnStaff.classList.add('active');
            document.getElementById('staff-login-inputs').style.display = 'block';
            document.getElementById('login-title').innerText = 'Staff Sign In';
        } else if (type === 'admin') {
            btnAdmin.classList.add('active');
            document.getElementById('admin-login-inputs').style.display = 'block';
            document.getElementById('login-title').innerText = 'Admin Sign In';
        }
    }

    function toggleSignup() {
        const login = document.getElementById('login-form');
        const signup = document.getElementById('signup-form');
        const loginDisplay = window.getComputedStyle(login).display;
        
        if (loginDisplay === 'none') {
            login.style.display = 'block';
            signup.style.display = 'none';
        } else {
            login.style.display = 'none';
            signup.style.display = 'block';
        }
    }

    // --- 2. NAVIGATION ---
    window.showSection = function(id) {
        document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
        const target = document.getElementById(id);
        if(target) target.style.display = 'block';
        
        document.querySelectorAll('.nav-links li').forEach(li => li.classList.remove('active'));
        const navMap = { 'dashboard': 0, 'appointments': 1, 'medicines': 2, 'prescriptions': 3, 'reports': 4, 'beds': 5, 'payments': 6 };
        const navItems = document.querySelectorAll('.nav-links li');
        if (navMap[id] !== undefined && navItems[navMap[id]]) {
            navItems[navMap[id]].classList.add('active');
        }
    };

    // --- 3. PAYMENT MODAL ---
    window.openPaymentModal = function(amount, desc, itemId, itemType) {
        const modal = document.getElementById('payment-modal');
        if(modal) {
            document.getElementById('pay-amount').innerText = '৳ ' + amount;
            document.getElementById('pay-desc').innerText = desc;
            document.getElementById('form-amount').value = amount;
            document.getElementById('form-item-id').value = itemId;
            document.getElementById('form-item-type').value = itemType;
            modal.style.display = 'block';
        }
    };
    window.closePaymentModal = function() {
        document.getElementById('payment-modal').style.display = 'none';
    };

    window.updatePrice = function() {
        const select = document.getElementById('doctor_select');
        const price = select.options[select.selectedIndex].getAttribute('data-price');
        document.getElementById('appt-price').innerText = '৳ ' + (price || 0);
    };

    // --- 4. DROPDOWN LOGIC ---
    function fetchData(action, params, targetSelectId, isBedContainer = false) {
        const formData = new FormData();
        formData.append('action', action);
        for(let key in params) formData.append(key, params[key]);

        fetch('get_dropdown_data.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                const target = document.getElementById(targetSelectId);
                target.innerHTML = data;
                if(!isBedContainer) {
                    target.disabled = false;
                    resetNext(targetSelectId);
                }
            });
    }

    function resetNext(currentId) {
        if(currentId.includes('city_select') && !currentId.includes('bed')) {
            const chain = ['city_select', 'area_select', 'hospital_select', 'department_select', 'doctor_select'];
            let start = false;
            chain.forEach(id => {
                if(start) { document.getElementById(id).innerHTML='<option value="">-- Select Previous --</option>'; document.getElementById(id).disabled=true; }
                if(id === currentId) start=true;
            });
        }
        if(currentId.includes('bed')) {
            const chain = ['bed_city_select', 'bed_area_select', 'bed_hosp_select'];
            let start = false;
            chain.forEach(id => {
                if(start) { document.getElementById(id).innerHTML='<option value="">-- Select Previous --</option>'; document.getElementById(id).disabled=true; }
                if(id === currentId) start=true;
            });
            if(currentId !== 'bed_hosp_select') {
                document.getElementById('bed_display_area').innerHTML = '<p style="grid-column: 1/-1; text-align:center;">Please select a hospital to view available beds.</p>';
            }
        }
    }

    if(document.getElementById('city_select')) {
        fetchData('get_cities', {}, 'city_select');
        document.getElementById('city_select').addEventListener('change', function() { if(this.value) fetchData('get_areas', {city: this.value}, 'area_select'); else resetNext('city_select'); });
        document.getElementById('area_select').addEventListener('change', function() { if(this.value) fetchData('get_hospitals', {area: this.value}, 'hospital_select'); else resetNext('area_select'); });
        document.getElementById('hospital_select').addEventListener('change', function() { if(this.value) fetchData('get_departments', {hospital_id: this.value}, 'department_select'); else resetNext('hospital_select'); });
        document.getElementById('department_select').addEventListener('change', function() { 
            const hospId = document.getElementById('hospital_select').value;
            if(this.value) fetchData('get_doctors', {hospital_id: hospId, department: this.value}, 'doctor_select'); else resetNext('department_select'); 
        });
        document.getElementById('doctor_select').addEventListener('change', window.updatePrice);
    }

    if(document.getElementById('bed_city_select')) {
        fetchData('get_cities', {}, 'bed_city_select');
        document.getElementById('bed_city_select').addEventListener('change', function() { 
            if(this.value) fetchData('get_areas', {city: this.value}, 'bed_area_select'); else resetNext('bed_city_select'); 
        });
        document.getElementById('bed_area_select').addEventListener('change', function() { 
            if(this.value) fetchData('get_hospitals', {area: this.value}, 'bed_hosp_select'); else resetNext('bed_area_select'); 
        });
        document.getElementById('bed_hosp_select').addEventListener('change', function() { 
            if(this.value) fetchData('get_beds', {hospital_id: this.value}, 'bed_display_area', true); else document.getElementById('bed_display_area').innerHTML = '';
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if(tab) window.showSection(tab);
});