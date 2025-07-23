(function(){
  function Autocomplete(textarea, autocompleteContainer) {
    const ta = textarea;
    const ac = autocompleteContainer;
    let items = [], selected = 0;

    function getWordInfo() {
      const pos = ta.selectionStart;
      const up = ta.value.slice(0, pos);
      const m = up.match(/(\w+)$/);
      if (!m) return null;
      return { word: m[1], start: m.index, end: pos };
    }

    function positionDropdown() {
      const { top, left, height } = ta.getBoundingClientRect();
      ac.style.top = (top + height + window.scrollY) + "px";
      ac.style.left = (left + window.scrollX) + "px";
      ac.style.width = ta.offsetWidth + "px"; // Match width of textarea
    }

    async function fetchMatches(q) {
      const res = await fetch(`/admin/ajax/shortcode_filter.php?q=${encodeURIComponent(q)}`);
      return res.ok ? res.json() : [];
    }

    function showList(list) {
      ac.innerHTML = "";
      list.forEach((it, i) => {
        const li = document.createElement("li");
        li.textContent = `${it.alias} → ${it.name}`;
        if (i === selected) li.classList.add("selected");
        li.addEventListener("mousedown", e => {
          pick(i);
          e.preventDefault();
        });
        ac.appendChild(li);
      });
      ac.style.display = list.length ? "block" : "none";
    }

    function pick(i) {
      const info = getWordInfo();
      if (!info) return hideList();
      const it = items[i];
      const before = ta.value.slice(0, info.start);
      const after = ta.value.slice(info.end);
      ta.value = before + it.expansion + after;
      const newPos = before.length + it.expansion.length;
      ta.setSelectionRange(newPos, newPos);
      hideList();
      // Manually trigger the input event so that the perspective fields update
      ta.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function hideList() {
      ac.style.display = "none";
      items = [];
      selected = 0;
    }

    ta.addEventListener("keydown", async e => {
      if (ac.style.display === "block") {
        if (e.key === "ArrowDown") {
          e.preventDefault();
          selected = Math.min(selected + 1, items.length - 1);
          showList(items);
          return;
        }
        if (e.key === "ArrowUp") {
          e.preventDefault();
          selected = Math.max(selected - 1, 0);
          showList(items);
          return;
        }
        if (e.key === "Enter" || e.key === "Tab") {
          e.preventDefault();
          pick(selected);
          return;
        }
        if (e.key === "Escape") {
          hideList();
          return;
        }
      }
    });

    ta.addEventListener("input", async () => {
      const info = getWordInfo();
      if (!info || info.word.length < 2) {
        return hideList();
      }
      items = await fetchMatches(info.word);
      if (items.length) {
        selected = 0;
        positionDropdown();
        showList(items);
      } else {
        hideList();
      }
    });

    document.addEventListener("click", e => {
      if (!ac.contains(e.target) && e.target !== ta) {
        hideList();
      }
    });
  }

  // Attach the autocomplete to all relevant textareas on the page
  document.querySelectorAll('.shortcodey-textarea').forEach(textarea => {
    const container = document.createElement('div');
    container.className = 'autocomplete-container';
    textarea.parentNode.insertBefore(container, textarea.nextSibling);
    new Autocomplete(textarea, container);
  });

})();
