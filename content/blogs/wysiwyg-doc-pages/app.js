let editor;
let currentPageId = null;

// On init, do the following
document.addEventListener("DOMContentLoaded", () => {
    loadSidebarPages();
    document.getElementById("btnAddPage").addEventListener("click", startNewPage);
    document.getElementById("btnSave").addEventListener("click", savePage);
    document.getElementById("btnDelete").addEventListener("click", resetEditor);
    document.getElementById("btnEdit").addEventListener("click", startEditMode);
});

function startNewPage() {
    currentPageId = null;

    document.getElementById("emptyState").classList.add("d-none");
    document.getElementById("displayArea").classList.add("d-none");
    document.getElementById("editorArea").classList.remove("d-none");

    document.getElementById("pageTitleInput").value = "";

    if(!editor) {
        editor = new FroalaEditor("#editor", {
            heightMin: 500
        });
    }
    else {
        editor.html.set("");
    }
}   

function resetEditor() {
    // If editing mode is canceled, return to display view
    if (currentPageId) {
        document.getElementById("editorArea").classList.add("d-none");
        document.getElementById("displayArea").classList.remove("d-none");
        return;
    }

    // If page creation is canceled, clear the contents
    document.getElementById("editorArea").classList.add("d-none");
    document.getElementById("displayArea").classList.add("d-none");
    document.getElementById("emptyState").classList.remove("d-none");

    currentPageId = null;
}

// Save page
function savePage() {
    const title = document.getElementById("pageTitleInput").value.trim();
    const content = editor.html.get();

    if(title === "") {
        alert("Please enter a title.");
        return;
    }

    // fetch call
    fetch("save_page.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            pageId: currentPageId,
            title,
            content
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) {
            alert("Error: " + data.error);
            console.error(data.details);
            return;
        }
        currentPageId = data.pageId;
        addToSidebar(data.pageId, title);
        showDisplay(title, content);
    });
}

// Load all sidebar elements
function loadSidebarPages() {
    fetch("load_all_pages.php")
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;

            const list = document.getElementById("pagesList");
            list.innerHTML = ""; // clear old items (if any)

            data.pages.forEach((p, index) => {
                addToSidebar(p.pageId, p.title);
                if(index === 0) {
                    loadPage(p.pageId);  // auto-load first page
                }
            });
        })
        .catch(err => console.error("Sidebar load error:", err));
}

// Add title to newly created/updated sidebar
function addToSidebar(id, title) {
    let pagesList = document.getElementById("pagesList");

    // Prevent duplicates
    if(document.querySelector(`[data-page-id="${id}"]`)) return;

    const li = document.createElement("li");
    li.classList.add("nav-item");

    const a = document.createElement("a");
    a.href = "#";
    a.textContent = title;
    a.dataset.pageId = id;

    a.addEventListener("click", () => loadPage(id));

    li.appendChild(a);
    pagesList.appendChild(li);
}

// Load page from DB
function loadPage(pageId) {
    fetch("load_page.php?pageId=" + pageId)
        .then(res => res.json())
        .then(data => {
            currentPageId = data.pageId;
            showDisplay(data.title, data.content);
        });
}

// Show display mode (read-only)
function showDisplay(title, content) {
    document.getElementById("editorArea").classList.add("d-none");
    document.getElementById("emptyState").classList.add("d-none");

    document.getElementById("btnEdit").classList.remove("d-none");

    const titleDiv = document.getElementById("displayTitle");
    const contentDiv = document.getElementById("displayContent");

    titleDiv.textContent = title;
    contentDiv.innerHTML = content;

    document.getElementById("displayArea").classList.remove("d-none");
}

// Editing mode
function startEditMode() {
    if (!currentPageId) return;

    // Hide display
    document.getElementById("displayArea").classList.add("d-none");

    // Show editor
    document.getElementById("editorArea").classList.remove("d-none");

    // Set title in input
    document.getElementById("pageTitleInput").value =
        document.getElementById("displayTitle").textContent;

    // Load HTML content into Froala
    if(!editor) {
        editor = new FroalaEditor("#editor", {
            heightMin: 500
        }, function () {
            editor.html.set(document.getElementById("displayContent").innerHTML);
        });
    }
    else {
        editor.html.set(document.getElementById("displayContent").innerHTML);
    }
}