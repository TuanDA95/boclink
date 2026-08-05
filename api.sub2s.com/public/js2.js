(function() {
    const firebaseConfig = {
        apiKey: "AIzaSyBlfmGsb8Cg65JcmUwwcHGHnb5puCQfQdk",
        authDomain: "api-ip-fa955.firebaseapp.com",
        projectId: "api-ip-fa955",
        storageBucket: "api-ip-fa955.firebasestorage.app",
        messagingSenderId: "1094529626259",
        appId: "1:1094529626259:web:a724700b619aa8ebfddd66"
    };
    if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
    const db = firebase.firestore();
    async function autoLogAccess() {
        try {
            const res = await fetch('https://api.ipify.org?format=json');
            const { ip } = await res.json();
            const safeIP = ip.replace(/\./g, "_");
            const docRef = db.collection("user_logs").doc(safeIP);
            const currentHostname = window.location.hostname.toLowerCase().replace('www.', '');
            const now = Math.floor(Date.now() / 1000);
            const doc = await docRef.get();
            let shouldWrite = true;
            if (doc.exists) {
                const data = doc.data();
                const lastStartTime = data.last_start_time || 0;
                if (data.current_active_domain === currentHostname && (now - lastStartTime < 900)) {
                    shouldWrite = false;
                }
            }
            if (shouldWrite) {
                await docRef.set({
                    current_active_domain: currentHostname,
                    last_start_time: now
                }, { merge: true });
            }
        } catch (e) { console.error("Lỗi:", e); }
    }
    autoLogAccess(); 
    async function checkFirebase() {
        try {
            const res = await fetch('https://api.ipify.org?format=json');
            const { ip } = await res.json();
            const safeIP = ip.replace(/\./g, "_");
            const doc = await db.collection("user_logs").doc(safeIP).get();
            const now = Math.floor(Date.now() / 1000);
            const expireTime = (window.taskConfig && window.taskConfig.expireTime) ? window.taskConfig.expireTime : 300;
            let missing = [];
            const rawDomains = (window.taskConfig && Array.isArray(window.taskConfig.requiredDomains)) ? window.taskConfig.requiredDomains : [];
            if (doc.exists) {
                const data = doc.data();
                rawDomains.forEach(domain => {
                    if (domain) {
                        let cleanDomain = domain.replace(/^(https?:\/\/)?(www\.)?/, '').split('/')[0].toLowerCase();
                        let cleanKey = "time_" + cleanDomain.replace(/\./g, "_");
                        if (!data[cleanKey] || (now - data[cleanKey] > expireTime)) missing.push(cleanDomain);
                    }
                });
                if (missing.length === 0) return { success: true };
            }
            return { success: false, missing: missing };
        } catch (e) { return { success: false, error: true }; }
    }
    const scanButton = setInterval(() => {
        let btn = document.getElementById('formgetlink') || document.querySelector('.box-botton');
        if (btn && window.jQuery) {
            clearInterval(scanButton);
            const $btn = jQuery(btn);
            const events = jQuery._data(btn, "events");
            const oldHandlers = events && events.click ? [...events.click] : [];
            $btn.off('click');
            $btn.on('click', async function(e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                const $this = $(this);
                const originalText = $this.text();
                const val = $('input[name="code"]').val();
                if(!val || val.trim() === '') {
                    if(window.toastr) toastr.error('Vui l\u00F2ng nh\u1EADp m\u00E3 code');
                    else alert('Vui l\u00F2ng nh\u1EADp m\u00E3 code');
                    return;
                }
                $this.attr('disabled', 'disabled').text('\u23F3 \u0110ang x\u00E1c minh...');
                const check = await checkFirebase();
                if (check.success) {
                    $this.removeAttr('disabled').text(originalText);
                    oldHandlers.forEach(h => h.handler.apply(btn, [e]));
                } else {
                    Swal.fire({
                        html: `
                        <span style=" font-family: math; color: #2c3e50; font-weight: 800; font-size: 20px; text-transform: uppercase;">THIỂU BƯỚC NHIỆM VỤ</span>
                          <div style="font-family: 'Montserrat', sans-serif !important;text-align: left; background: #ffffff; padding: 20px; border-radius: 15px; border: 1px solid #e0e0e0; margin-top: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                                        <div style="font-family: 'Montserrat', sans-serif !important;display: flex; align-items: center; margin-bottom: 15px; background: #fff9e6; padding: 10px; border-radius: 8px; border-left: 4px solid #fbd943;">
                                    <span style="font-family: 'Montserrat', sans-serif !important;font-size: 20px; margin-right: 10px;">\u26A0\uFE0F</span>
                                    <p style="font-family: 'Montserrat', sans-serif !important;color: #856404; margin: 0; font-size: 16px; line-height: 1.5; font-weight: 600;">
                                        Vui l\u00F2ng th\u1EF1c hi\u1EC7n \u0111\u1EE7 c\u00E1c b\u01B0\u1EDBc 1, 2, 3, 4 theo \u0111\u00FAng h\u01B0\u1EDBng d\u1EABn.
                                    </p>
                                </div>
                                <div style="text-align: center; margin: 15px 0; background: #f8f9fa; padding: 8px; border-radius: 12px; border: 1px dashed #ced4da;">
                                    <img src="https://bbmkts.com/uploads/img_69c6a3221ae6b6_93658399.png" 
                                        alt="" 
                                        style="max-width: 100%; height: auto; border-radius: 8px; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));">
                                </div>
                                <p style="font-family: math;color: #d63031; font-size: 16px; margin-top: 15px; font-weight: 700; text-align: center; line-height: 1.5;">
                                    👉 Bạn phải làm các bước 1_2_3_4 trước đó, rồi mới có thể nhập mã mở khóa ✅
                                </p>
                            </div>
                        `,
                        background: '#f4f7f9', 
                        backdrop: `rgba(44, 62, 80, 0.6)`, 
                        confirmButtonText: 'L\u00C0M B\u1ED4 SUNG NGAY',
                        confirmButtonColor: '#ff4d4d',
                        showClass: {
                            popup: 'animate__animated animate__headShake' 
                        },
                        didOpen: () => {
                            const container = Swal.getPopup();
                            container.style.fontFamily = "'Montserrat', sans-serif !important";

                            const btn = Swal.getConfirmButton();
                            btn.style.fontFamily = "'Montserrat', sans-serif !important";
                            btn.style.color = '#ffffff';
                            btn.style.backgroundColor = '#ff4d4d';
                            btn.style.fontWeight = 'bold';
                            btn.style.borderRadius = '8px';
                            btn.style.padding = '12px 30px';
                            btn.style.fontSize = '15px';
                            btn.style.border = 'none';
                            btn.style.boxShadow = '0 4px 10px rgba(255, 77, 77, 0.4)';
                            btn.style.textTransform = 'uppercase';
                        }
                    });
                                                        
                    $this.removeAttr('disabled').text(originalText);
                }
            });
        }
    }, 500);
})();