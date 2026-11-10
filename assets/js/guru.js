const sections = document.querySelectorAll(".page-section");
const navLinks = document.querySelectorAll("[data-section-link]");
const goToButtons = document.querySelectorAll("[data-go-to]");

const rppData = [
    {
        id: 1,
        title: "Rencana Pembelajaran Informatika Kelas X",
        year: "2026/2027",
        semester: "Ganjil",
        fileName: "rencana-pembelajaran-informatika-x.pdf"
    }
];

const bankSoalData = [
    {
        id: 1,
        title: "Bank Soal Informatika - Penilaian Tengah Semester",
        year: "2026/2027",
        semester: "Kelas X",
        fileName: "bank-soal-informatika-pts.pdf"
    }
];

const laporanData = [
    {
        id: 1,
        title: "Laporan Kinerja Guru",
        year: "2025/2026",
        semester: "Tahunan",
        fileName: "laporan-kinerja-guru-2025-2026.pdf"
    }
];

let activeEditType = "";
let activeEditId = null;

function bukaBagian(sectionId) {
    sections.forEach(function (section) {
        section.classList.toggle("active", section.id === sectionId);
    });

    navLinks.forEach(function (link) {
        link.classList.toggle(
            "active",
            link.dataset.sectionLink === sectionId
        );
    });

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

navLinks.forEach(function (link) {
    link.addEventListener("click", function (event) {
        event.preventDefault();
        bukaBagian(link.dataset.sectionLink);
    });
});

goToButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        bukaBagian(button.dataset.goTo);
    });
});

function buatInisial(nama) {
    const kata = nama
        .replace(/[^a-zA-Z\s]/g, "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (kata.length === 0) {
        return "NG";
    }

    if (kata.length === 1) {
        return kata[0].slice(0, 2).toUpperCase();
    }

    return (kata[0][0] + kata[1][0]).toUpperCase();
}

function updateProfilGuru() {
    const nama = document.getElementById("teacherName").value.trim();
    const mapel = document.getElementById("teacherSubject").value.trim();
    const jabatan = document.getElementById("teacherPosition").value.trim();

    const namaTampil = nama || "Nama Guru";
    const posisiTampil = mapel || jabatan || "Guru Mata Pelajaran";
    const inisial = buatInisial(namaTampil);

    document.getElementById("headerTeacherName").textContent = namaTampil;
    document.getElementById("profileNamePreview").textContent = namaTampil;
    document.getElementById("profileMapelPreview").textContent = posisiTampil;
    document.getElementById("headerInitial").textContent = inisial;
    document.getElementById("profileAvatar").textContent = inisial;
}

document.getElementById("profileForm").addEventListener("submit", function (event) {
    event.preventDefault();

    updateProfilGuru();

    document.getElementById("profileStatus").textContent = "Tersimpan";
    document.getElementById("profileMessage").textContent =
        "Perubahan profil berhasil disimpan untuk tampilan frontend.";

    setTimeout(function () {
        document.getElementById("profileMessage").textContent = "";
    }, 3500);
});

function getDataByType(type) {
    if (type === "rpp") {
        return rppData;
    }

    if (type === "bank-soal") {
        return bankSoalData;
    }

    return laporanData;
}

function getListId(type) {
    if (type === "rpp") {
        return "rppList";
    }

    if (type === "bank-soal") {
        return "bankSoalList";
    }

    return "laporanList";
}

function updateCounter() {
    document.getElementById("rppCount").textContent = rppData.length;
    document.getElementById("bankSoalCount").textContent = bankSoalData.length;
    document.getElementById("laporanCount").textContent = laporanData.length;
}

function renderDocuments(type) {
    const data = getDataByType(type);
    const list = document.getElementById(getListId(type));

    list.innerHTML = "";

    if (data.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                Belum ada dokumen PDF.<br>
                Gunakan formulir di sebelah kiri untuk menambahkan dokumen.
            </div>
        `;
        return;
    }

    data.forEach(function (documentItem) {
        const element = document.createElement("article");

        element.className = "document-item";

        element.innerHTML = `
            <div class="document-head">
                <div class="document-main">
                    <span class="pdf-icon">PDF</span>

                    <div>
                        <h3>${escapeHtml(documentItem.title)}</h3>

                        <p>
                            ${escapeHtml(documentItem.year)} ·
                            ${escapeHtml(documentItem.semester)}<br>
                            File: ${escapeHtml(documentItem.fileName)}
                        </p>
                    </div>
                </div>
            </div>

            <div class="document-actions">
                <button
                    class="small-button"
                    type="button"
                    data-edit-type="${type}"
                    data-edit-id="${documentItem.id}"
                >
                    Edit
                </button>

                <button
                    class="small-button danger"
                    type="button"
                    data-delete-type="${type}"
                    data-delete-id="${documentItem.id}"
                >
                    Hapus
                </button>
            </div>
        `;

        list.appendChild(element);
    });

    list.querySelectorAll("[data-edit-type]").forEach(function (button) {
        button.addEventListener("click", function () {
            bukaModalEdit(
                button.dataset.editType,
                Number(button.dataset.editId)
            );
        });
    });

    list.querySelectorAll("[data-delete-type]").forEach(function (button) {
        button.addEventListener("click", function () {
            hapusDokumen(
                button.dataset.deleteType,
                Number(button.dataset.deleteId)
            );
        });
    });
}

function escapeHtml(text) {
    const temporary = document.createElement("div");
    temporary.textContent = text;
    return temporary.innerHTML;
}

function tambahDokumen(type, title, year, semester, file) {
    const data = getDataByType(type);

    data.unshift({
        id: Date.now(),
        title: title,
        year: year,
        semester: semester,
        fileName: file.name
    });

    renderDocuments(type);
    updateCounter();
}

function cekPdf(file) {
    if (!file) {
        return "Pilih file PDF terlebih dahulu.";
    }

    const namaFile = file.name.toLowerCase();

    if (!namaFile.endsWith(".pdf")) {
        return "File yang dipilih harus berformat PDF.";
    }

    return "";
}

document.getElementById("rppForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("rppTitle").value.trim();
    const year = document.getElementById("rppYear").value.trim();
    const semester = document.getElementById("rppSemester").value;
    const file = document.getElementById("rppFile").files[0];
    const message = document.getElementById("rppMessage");

    const error = cekPdf(file);

    if (error) {
        message.textContent = error;
        return;
    }

    tambahDokumen("rpp", title, year, semester, file);

    message.textContent =
        "Rencana pembelajaran PDF berhasil ditambahkan pada tampilan.";

    event.target.reset();
    document.getElementById("rppYear").value = "2026/2027";

    setTimeout(function () {
        message.textContent = "";
    }, 3500);
});

document.getElementById("bankSoalForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("bankSoalTitle").value.trim();
    const kelas = document.getElementById("bankSoalClass").value.trim();
    const year = document.getElementById("bankSoalYear").value.trim();
    const file = document.getElementById("bankSoalFile").files[0];
    const message = document.getElementById("bankSoalMessage");

    const error = cekPdf(file);

    if (error) {
        message.textContent = error;
        return;
    }

    tambahDokumen("bank-soal", title, year, kelas, file);

    message.textContent =
        "Bank soal PDF berhasil ditambahkan pada tampilan.";

    event.target.reset();
    document.getElementById("bankSoalYear").value = "2026/2027";

    setTimeout(function () {
        message.textContent = "";
    }, 3500);
});

document.getElementById("laporanForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("laporanTitle").value.trim();
    const year = document.getElementById("laporanYear").value.trim();
    const semester = document.getElementById("laporanSemester").value;
    const file = document.getElementById("laporanFile").files[0];
    const message = document.getElementById("laporanMessage");

    const error = cekPdf(file);

    if (error) {
        message.textContent = error;
        return;
    }

    tambahDokumen("laporan", title, year, semester, file);

    message.textContent =
        "Laporan kinerja PDF berhasil ditambahkan pada tampilan.";

    event.target.reset();
    document.getElementById("laporanYear").value = "2026/2027";

    setTimeout(function () {
        message.textContent = "";
    }, 3500);
});

const modal = document.getElementById("editDocumentModal");
const editForm = document.getElementById("editDocumentForm");

function bukaModalEdit(type, id) {
    const data = getDataByType(type);
    const documentItem = data.find(function (item) {
        return item.id === id;
    });

    if (!documentItem) {
        return;
    }

    activeEditType = type;
    activeEditId = id;

    document.getElementById("modalTitle").textContent =
        `Edit: ${documentItem.title}`;

    document.getElementById("editTitle").value = documentItem.title;
    document.getElementById("editYear").value = documentItem.year;
    document.getElementById("editSemester").value = documentItem.semester;
    document.getElementById("editFile").value = "";

    modal.hidden = false;
}

function tutupModal() {
    modal.hidden = true;
    activeEditType = "";
    activeEditId = null;
}

document.querySelectorAll("[data-close-modal]").forEach(function (button) {
    button.addEventListener("click", tutupModal);
});

editForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const data = getDataByType(activeEditType);
    const documentItem = data.find(function (item) {
        return item.id === activeEditId;
    });

    if (!documentItem) {
        tutupModal();
        return;
    }

    const newFile = document.getElementById("editFile").files[0];

    if (newFile) {
        const error = cekPdf(newFile);

        if (error) {
            alert(error);
            return;
        }

        documentItem.fileName = newFile.name;
    }

    documentItem.title = document.getElementById("editTitle").value.trim();
    documentItem.year = document.getElementById("editYear").value.trim();
    documentItem.semester =
        document.getElementById("editSemester").value.trim();

    renderDocuments(activeEditType);
    updateCounter();
    tutupModal();
});

function hapusDokumen(type, id) {
    const yakin = confirm("Apakah Anda yakin ingin menghapus dokumen ini?");

    if (!yakin) {
        return;
    }

    const data = getDataByType(type);
    const index = data.findIndex(function (item) {
        return item.id === id;
    });

    if (index !== -1) {
        data.splice(index, 1);
        renderDocuments(type);
        updateCounter();
    }
}

renderDocuments("rpp");
renderDocuments("bank-soal");
renderDocuments("laporan");
updateCounter();
updateProfilGuru();