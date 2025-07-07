(function(){
  const ta = document.getElementById("shortcodey");
  const ac = document.getElementById("autocomplete");
  let items = [], selected = 0;

  // helper: get current word before caret
  function getWordInfo() {
    const pos = ta.selectionStart;
    const up = ta.value.slice(0, pos);
    // split on non-word to find last token
    const m = up.match(/(\w+)$/);
    if (!m) return null;
    return { word: m[1], start: m.index, end: pos };
  }

  // position the dropdown roughly at caret
  function positionDropdown() {
    const { top, left } = ta.getBoundingClientRect();
    // quick hack: put it at bottom-left of textarea + scroll
    ac.style.top = (top + ta.offsetHeight + window.scrollY) + "px";
    ac.style.left = (left + window.scrollX + 2) + "px";
  }

  // fetch matches from server
  async function fetchMatches(q) {
    const res = await fetch(`/admin/ajax/shortcode_filter.php?q=${encodeURIComponent(q)}`);
    return res.ok ? res.json() : [];
  }

  // render dropdown
  function showList(list) {
    ac.innerHTML = "";
    list.forEach((it,i) => {
      const li = document.createElement("li");
      li.textContent = `${it.alias} → ${it.name}`;
      if(i===selected) li.classList.add("selected");
      li.addEventListener("mousedown", e => {
        // on click: select this item
        pick(i);
        e.preventDefault();
      });
      ac.appendChild(li);
    });
    ac.style.display = list.length ? "block" : "none";
  }

  // insert the selected item into textarea
  function pick(i) {
    const info = getWordInfo();
    if (!info) return hideList();
    const it = items[i];
    const before = ta.value.slice(0, info.start);
    const after  = ta.value.slice(info.end);
    ta.value = before + it.expansion + after;
    const newPos = before.length + it.expansion.length;
    ta.setSelectionRange(newPos, newPos);
    hideList();
  }

  function hideList() {
    ac.style.display = "none";
    items = [];
    selected = 0;
  }

  // main key handlers
  ta.addEventListener("keydown", async e => {
    if (ac.style.display === "block") {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        selected = Math.min(selected+1, items.length-1);
        showList(items);
        return;
      }
      if (e.key === "ArrowUp") {
        e.preventDefault();
        selected = Math.max(selected-1, 0);
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
    // otherwise let the key go through, and on next tick we'll trigger input
  });

  ta.addEventListener("input", async () => {
    const info = getWordInfo();
    if (!info || info.word.length < 2) {
      return hideList();
    }

    // fire AJAX
    items = await fetchMatches(info.word);
    if (items.length) {
      selected = 0;
      positionDropdown();
      showList(items);
    }
    else hideList();
  });

  // click outside to close
  document.addEventListener("click", e => {
    if (!ac.contains(e.target) && e.target !== ta) hideList();
  });
})();