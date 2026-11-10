document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll(".page-section");
    const navLinks = document.querySelectorAll("[data-section-link]");
    const goToButtons = document.querySelectorAll("[data-go-to]");

    function bukaBagian(sectionId) {
        const sectionTujuan = document.getElementById(sectionId);

        if (!sectionTujuan) {
            console.error(
                "Section dengan id '" + sectionId + "' tidak ditemukan."
            );
            return;
        }

        sections.forEach(function (section) {
            section.classList.remove("active");
        });

        sectionTujuan.classList.add("active");

        navLinks.forEach(function (link) {
            const isActive = link.dataset.sectionLink === sectionId;

            link.classList.toggle("active", isActive);
        });

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    navLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            const sectionId = link.dataset.sectionLink;

            if (sectionId) {
                bukaBagian(sectionId);
            }
        });
    });

    goToButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const sectionId = button.dataset.goTo;

            if (sectionId) {
                bukaBagian(sectionId);
            }
        });
    });

    function buatInisial(nama) {
        const kata = nama
            .replace(/[^a-zA-Z\s]/g, "")
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (kata.length === 0) {
            return "NS";
        }

        if (kata.length === 1) {
            return kata[0].slice(0, 2).toUpperCase();
        }

        return (kata[0][0] + kata[1][0]).toUpperCase();
    }

    function updateProfilSiswa() {
        const inputNama = document.getElementById("studentName");
        const inputKelas = document.getElementById("studentClass");

        if (!inputNama || !inputKelas) {
            return;
        }

        const nama = inputNama.value.trim();
        const kelas = inputKelas.value.trim();

        const namaTampil = nama || "Nama Siswa";
        const kelasTampil = kelas || "Kelas belum diisi";
        const inisial = buatInisial(namaTampil);

        const headerStudentName = document.getElementById("headerStudentName");
        const profileNamePreview = document.getElementById("profileNamePreview");
        const profileClassPreview = document.getElementById("profileClassPreview");
        const headerInitial = document.getElementById("headerInitial");
        const profileAvatar = document.getElementById("profileAvatar");
        const statClass = document.getElementById("statClass");

        if (headerStudentName) {
            headerStudentName.textContent = namaTampil;
        }

        if (profileNamePreview) {
            profileNamePreview.textContent = namaTampil;
        }

        if (profileClassPreview) {
            profileClassPreview.textContent = "Kelas " + kelasTampil;
        }

        if (headerInitial) {
            headerInitial.textContent = inisial;
        }

        if (profileAvatar) {
            profileAvatar.textContent = inisial;
        }

        if (statClass) {
            statClass.textContent = kelasTampil;
        }
    }

    const profileForm = document.getElementById("studentProfileForm");

    if (profileForm) {
        profileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            updateProfilSiswa();

            const profileMessage = document.getElementById("profileMessage");

            if (profileMessage) {
                profileMessage.textContent =
                    "Perubahan profil berhasil disimpan untuk tampilan frontend.";

                setTimeout(function () {
                    profileMessage.textContent = "";
                }, 3500);
            }
        });
    }

    const jadwalMingguan = {
        senin: [
            {
                jam: "07.30",
                mapel: "Informatika",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 1"
            },
            {
                jam: "09.30",
                mapel: "Matematika",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 2"
            },
            {
                jam: "11.00",
                mapel: "Bahasa Indonesia",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 3"
            }
        ],

        selasa: [
            {
                jam: "07.30",
                mapel: "Bahasa Inggris",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 1"
            },
            {
                jam: "09.30",
                mapel: "Biologi",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 2"
            },
            {
                jam: "11.00",
                mapel: "Pendidikan Pancasila",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 3"
            }
        ],

        rabu: [
            {
                jam: "07.30",
                mapel: "Kimia",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 1"
            },
            {
                jam: "09.30",
                mapel: "Fisika",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 2"
            },
            {
                jam: "11.00",
                mapel: "Sejarah",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 3"
            }
        ],

        kamis: [
            {
                jam: "07.30",
                mapel: "Matematika",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 1"
            },
            {
                jam: "09.30",
                mapel: "Informatika",
                detail: "Laboratorium Komputer · Nama Guru",
                label: "Pelajaran 2"
            },
            {
                jam: "11.00",
                mapel: "Seni Budaya",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 3"
            }
        ],

        jumat: [
            {
                jam: "07.30",
                mapel: "Pendidikan Jasmani",
                detail: "Lapangan Sekolah · Nama Guru",
                label: "Pelajaran 1"
            },
            {
                jam: "09.30",
                mapel: "Pendidikan Agama",
                detail: "Ruang Kelas · Nama Guru",
                label: "Pelajaran 2"
            }
        ]
    };

    function tampilkanJadwal(hari) {
        const daftarJadwal = document.getElementById("fullScheduleList");

        if (!daftarJadwal) {
            return;
        }

        const jadwal = jadwalMingguan[hari] || [];

        daftarJadwal.innerHTML = "";

        if (jadwal.length === 0) {
            daftarJadwal.innerHTML =
                "<p>Belum ada jadwal untuk hari tersebut.</p>";
            return;
        }

        jadwal.forEach(function (item) {
            const jadwalItem = document.createElement("article");

            jadwalItem.className = "full-schedule-item";

            const waktu = document.createElement("time");
            waktu.textContent = item.jam;

            const isi = document.createElement("div");

            const mapel = document.createElement("h3");
            mapel.textContent = item.mapel;

            const detail = document.createElement("p");
            detail.textContent = item.detail;

            isi.appendChild(mapel);
            isi.appendChild(detail);

            const label = document.createElement("span");
            label.className = "status-pill";
            label.textContent = item.label;

            jadwalItem.appendChild(waktu);
            jadwalItem.appendChild(isi);
            jadwalItem.appendChild(label);

            daftarJadwal.appendChild(jadwalItem);
        });
    }

    const dayButtons = document.querySelectorAll(".day-button");

    dayButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            dayButtons.forEach(function (item) {
                item.classList.remove("active");
            });

            button.classList.add("active");

            tampilkanJadwal(button.dataset.day);
        });
    });

    const yearElement = document.getElementById("year");

    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    tampilkanJadwal("senin");
    updateProfilSiswa();
});