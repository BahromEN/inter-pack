/* ==========================================================================
   INTER PACK — shared behaviour: language switching, header, reveal-on-scroll
   ========================================================================== */

(function(){
  "use strict";

  var LANG_KEY = "ip_lang";
  var SUPPORTED = ["ru","en","uz"];

  function getLang(){
    var stored = null;
    try{ stored = localStorage.getItem(LANG_KEY); }catch(e){}
    if(stored && SUPPORTED.indexOf(stored) !== -1) return stored;
    return "ru";
  }

  function setLang(lang){
    if(SUPPORTED.indexOf(lang) === -1) lang = "ru";
    try{ localStorage.setItem(LANG_KEY, lang); }catch(e){}
    document.documentElement.setAttribute("lang", lang === "uz" ? "uz" : lang);
    applyTranslations(lang);
    document.querySelectorAll(".lang-switch button").forEach(function(btn){
      btn.classList.toggle("active", btn.getAttribute("data-lang") === lang);
    });
    document.dispatchEvent(new CustomEvent("ip:langchange", {detail:{lang:lang}}));
  }

  function resolvePath(dict, path){
    var parts = path.split(".");
    var cur = dict;
    for(var i=0;i<parts.length;i++){
      if(cur == null) return undefined;
      cur = cur[parts[i]];
    }
    return cur;
  }

  window.ipT = function(path, lang){
    lang = lang || getLang();
    var dict = window.TRANSLATIONS[lang] || window.TRANSLATIONS.ru;
    var val = resolvePath(dict, path);
    if(val === undefined) val = resolvePath(window.TRANSLATIONS.ru, path);
    return val;
  };
  window.ipLang = getLang;

  function applyTranslations(lang){
    document.querySelectorAll("[data-i18n]").forEach(function(el){
      var val = window.ipT(el.getAttribute("data-i18n"), lang);
      if(typeof val === "string" || typeof val === "number") el.textContent = val;
    });
    document.querySelectorAll("[data-i18n-ph]").forEach(function(el){
      var val = window.ipT(el.getAttribute("data-i18n-ph"), lang);
      if(typeof val === "string") el.setAttribute("placeholder", val);
    });
    document.querySelectorAll("[data-i18n-title]").forEach(function(el){
      var val = window.ipT(el.getAttribute("data-i18n-title"), lang);
      if(typeof val === "string") document.title = val;
    });
  }

  function initHeader(){
    var header = document.querySelector(".site-header");
    var burger = document.getElementById("burgerBtn");
    var nav = document.getElementById("mainNav");
    if(header){
      var onScroll = function(){
        header.classList.toggle("is-scrolled", window.scrollY > 8);
      };
      onScroll();
      window.addEventListener("scroll", onScroll, {passive:true});
    }
    if(burger && nav){
      burger.addEventListener("click", function(){
        var open = burger.classList.toggle("is-open");
        nav.classList.toggle("is-open", open);
        document.body.style.overflow = open ? "hidden" : "";
      });
      nav.querySelectorAll("a").forEach(function(a){
        a.addEventListener("click", function(){
          burger.classList.remove("is-open");
          nav.classList.remove("is-open");
          document.body.style.overflow = "";
        });
      });
    }
    document.querySelectorAll(".lang-switch button").forEach(function(btn){
      btn.addEventListener("click", function(){
        setLang(btn.getAttribute("data-lang"));
      });
    });
  }

  function initReveal(){
    var targets = document.querySelectorAll(".reveal, .reveal-stagger");
    if(!("IntersectionObserver" in window)){
      targets.forEach(function(el){ el.classList.add("in-view"); });
      return;
    }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add("in-view");
          io.unobserve(entry.target);
        }
      });
    }, {threshold:0.12, rootMargin:"0px 0px -40px 0px"});
    targets.forEach(function(el){ io.observe(el); });
  }

  document.addEventListener("DOMContentLoaded", function(){
    initHeader();
    initReveal();
    var lang = getLang();
    document.documentElement.setAttribute("lang", lang === "uz" ? "uz" : lang);
    applyTranslations(lang);
    document.querySelectorAll(".lang-switch button").forEach(function(btn){
      btn.classList.toggle("active", btn.getAttribute("data-lang") === lang);
    });
    document.dispatchEvent(new CustomEvent("ip:langchange", {detail:{lang:lang}}));
  });

})();
