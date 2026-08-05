(function() {
    const s1 = document.createElement('script');
    s1.src = "https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js";
    const s2 = document.createElement('script');
    s2.src = "https://www.gstatic.com/firebasejs/8.10.1/firebase-firestore.js";
    document.head.appendChild(s1);
    document.head.appendChild(s2);

    s2.onload = async function() {
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

        try {
            const res = await fetch('https://api.ipify.org?format=json');
            const { ip } = await res.json();
            const safeIP = ip.replace(/\./g, "_");
            const docRef = db.collection("user_logs").doc(safeIP);
            
            const doc = await docRef.get();
            if (doc.exists) {
                const data = doc.data();
                const currentDomain = window.location.hostname.toLowerCase().replace('www.', '');
                const storageKey = "time_" + currentDomain.replace(/\./g, "_");
                const now = Math.floor(Date.now() / 1000);
                const lastTaskTime = data[storageKey] || 0;

                if (data.current_active_domain && (now - lastTaskTime > 300)) {
                    await docRef.set({
                        [storageKey]: now,
                        last_update: firebase.firestore.FieldValue.serverTimestamp()
                    }, { merge: true });
                }
            }
        } catch (e) { console.error(e); }
    };
})();