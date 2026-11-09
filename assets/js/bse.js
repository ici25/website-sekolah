document.addEventListener("DOMContentLoaded", function () {
    const searchBook = document.getElementById("searchBook");
    const filterClass = document.getElementById("filterClass");
    const filterSubject = document.getElementById("filterSubject");
    const resetFilter = document.getElementById("resetFilter");

    const bookCards = document.querySelectorAll(".book-card");
    const bookCount = document.getElementById("bookCount");
    const emptyState = document.getElementById("emptyState");

    const modal = document.getElementById("bookModal");
    const modalBookTitle = document.getElementById("modalBookTitle");

    function filterBooks() {
        const keyword = searchBook.value.toLowerCase().trim();
        const selectedClass = filterClass.value;
        const selectedSubject = filterSubject.value;

        let totalVisible = 0;

        bookCards.forEach(function (book) {
            const bookTitle = book.dataset.title.toLowerCase();
            const bookSubject = book.dataset.subject.toLowerCase();
            const bookClass = book.dataset.class;

            const cocokKataKunci =
                bookTitle.includes(keyword) ||
                bookSubject.includes(keyword);

            const cocokKelas =
                selectedClass === "semua" ||
                bookClass === selectedClass;

            const cocokMapel =
                selectedSubject === "semua" ||
                book.dataset.subject === selectedSubject;

            const shouldShow =
                cocokKataKunci &&
                cocokKelas &&
                cocokMapel;

            book.classList.toggle("hidden", !shouldShow);

            if (shouldShow) {
                totalVisible++;
            }
        });

        bookCount.textContent =
            "Menampilkan " + totalVisible + " buku";

        emptyState.hidden = totalVisible !== 0;
    }

    searchBook.addEventListener("input", filterBooks);
    filterClass.addEventListener("change", filterBooks);
    filterSubject.addEventListener("change", filterBooks);

    resetFilter.addEventListener("click", function () {
        searchBook.value = "";
        filterClass.value = "semua";
        filterSubject.value = "semua";

        filterBooks();
    });

    document.querySelectorAll(".open-book-button").forEach(function (button) {
        button.addEventListener("click", function () {
            const title = button.dataset.bookTitle;

            modalBookTitle.textContent = title;
            modal.hidden = false;
        });
    });

    document.querySelectorAll("[data-close-modal]").forEach(function (button) {
        button.addEventListener("click", function () {
            modal.hidden = true;
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            modal.hidden = true;
        }
    });

    const year = document.getElementById("year");

    if (year) {
        year.textContent = new Date().getFullYear();
    }

    filterBooks();
});