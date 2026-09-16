// ==========================================
// NAVEGAÇÃO ENTRE AS TELAS
// ==========================================

function showPage(pageId) {

    // Esconde todas as páginas
    const pages = document.querySelectorAll(".page");

    pages.forEach(page => {
        page.classList.remove("active");
    });

    // Mostra a página escolhida
    const selectedPage = document.getElementById(pageId);

    if (selectedPage) {
        selectedPage.classList.add("active");
    }

    // Volta para o topo
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

    // Atualiza a URL sem trocar de arquivo
    history.pushState(null, "", "#" + pageId);
}


// ==========================================
// LINKS DO MENU
// ==========================================

document.querySelectorAll("[data-page]").forEach(link => {

    link.addEventListener("click", function(event) {

        event.preventDefault();

        const page = this.getAttribute("data-page");

        showPage(page);

    });

});


// ==========================================
// ABRIR A PÁGINA PELO # DA URL
// ==========================================

function loadPageFromURL() {

    const page = window.location.hash.replace("#", "");

    if (page && document.getElementById(page)) {

        document.querySelectorAll(".page").forEach(p => {
            p.classList.remove("active");
        });

        document.getElementById(page).classList.add("active");

    } else {

        document.getElementById("inicio").classList.add("active");

    }
}

loadPageFromURL();


// ==========================================
// BOTÃO VOLTAR DO NAVEGADOR
// ==========================================

window.addEventListener("popstate", function() {

    loadPageFromURL();

});


// ==========================================
// LOGIN
// ==========================================

const loginForm = document.getElementById("loginForm");

if (loginForm) {

    loginForm.addEventListener("submit", function(event) {

        event.preventDefault();

        const email = document.getElementById("loginEmail").value;

        if (email.trim() === "") {
            return;
        }

        openModal(
            "Login realizado!",
            "Bem-vindo à ParkPoint. Você já pode procurar uma vaga."
        );

        setTimeout(() => {
            closeModal();
            showPage("comecar");
        }, 1800);

    });

}


// ==========================================
// FORMULÁRIO DE CONTATO
// ==========================================

const contactForm = document.getElementById("contactForm");

if (contactForm) {

    contactForm.addEventListener("submit", function(event) {

        event.preventDefault();

        const name = document.getElementById("contactName").value;

        openModal(
            "Mensagem enviada!",
            "Obrigado, " + name + ". Entraremos em contato em breve."
        );

        contactForm.reset();

    });

}


// ==========================================
// BUSCA DE ESTACIONAMENTO
// ==========================================

function searchParking() {

    const destination =
        document.getElementById("destination").value;

    const distance =
        document.getElementById("distance").value;

    const message =
        document.getElementById("searchMessage");


    if (destination.trim() === "") {

        message.textContent =
            "Digite um destino para realizar a busca.";

        return;
    }


    message.textContent =
        "Encontramos estacionamentos próximos de " +
        destination +
        " em até " +
        distance +
        " km.";

}


// ==========================================
// RESERVA
// ==========================================

function reserve(parkingName) {

    openModal(
        "Reserva realizada!",
        "Sua vaga no " +
        parkingName +
        " foi reservada com sucesso."
    );

}


// ==========================================
// MODAL
// ==========================================

function openModal(title, message) {

    const modal =
        document.getElementById("modal");

    const modalTitle =
        document.getElementById("modalTitle");

    const modalMessage =
        document.getElementById("modalMessage");


    modalTitle.textContent = title;

    modalMessage.textContent = message;

    modal.classList.add("show");

}


function closeModal() {

    const modal =
        document.getElementById("modal");

    modal.classList.remove("show");

}


// ==========================================
// FECHAR MODAL CLICANDO FORA
// ==========================================

document.getElementById("modal").addEventListener(
    "click",
    function(event) {

        if (event.target === this) {
            closeModal();
        }

    }
);