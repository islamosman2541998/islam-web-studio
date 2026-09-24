let pageAbort,instances=[],observer;let lastFocus;
const reduced=()=>window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const pauseVideos=()=>document.querySelectorAll('video').forEach(video=>video.pause());
function revealElements(){observer?.disconnect();const elements=[...document.querySelectorAll('[data-reveal]')];if(!elements.length)return;document.documentElement.classList.add('reveal-ready');if(reduced()||!('IntersectionObserver'in window)){elements.forEach(element=>element.classList.add('is-revealed'));return;}observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-revealed');observer?.unobserve(entry.target);}}),{threshold:.14,rootMargin:'0px 0px -40px'});elements.forEach(element=>observer.observe(element));}
function hideLoader(){const el=document.getElementById('preloader');if(!el)return;if(reduced()){el.remove();return;}const duration=Math.max(100,Math.min(1200,Number(document.body.dataset.transitionDuration)||400));const delay=Math.max(0,Math.min(5000,Number(document.body.dataset.loaderDelay)||0));setTimeout(()=>{el.classList.add('is-leaving');setTimeout(()=>el.remove(),duration+60);},delay);}
function closeLightbox(){const dialog=document.getElementById('gallery-lightbox');dialog?.querySelectorAll('video').forEach(v=>v.pause());dialog?.close();document.body.classList.remove('lightbox-open');lastFocus?.focus();}
function openGallery(items,button){const dialog=document.getElementById('gallery-lightbox');if(!dialog||!items?.length)return;lastFocus=button;let index=0;const content=dialog.querySelector('.lightbox-content');const show=()=>{content.querySelectorAll('video').forEach(v=>v.pause());content.replaceChildren();const item=items[index];if(!/^https?:\/\//.test(item.src))return;const el=document.createElement(item.type==='video'?'video':'img');el.src=item.src;if(el.tagName==='VIDEO'){el.controls=true;el.playsInline=true;el.preload='metadata';}else{el.alt=item.alt||'';}content.append(el);dialog.querySelector('[data-lightbox-count]').textContent=(index+1)+' / '+items.length;dialog.querySelector('.lightbox-head span').textContent=item.alt||'';};dialog.querySelector('[data-lightbox-prev]').onclick=()=>{index=(index-1+items.length)%items.length;show();};dialog.querySelector('[data-lightbox-next]').onclick=()=>{index=(index+1)%items.length;show();};dialog.onkeydown=e=>{if(e.key==='ArrowRight')dialog.querySelector('[data-lightbox-next]').click();if(e.key==='ArrowLeft')dialog.querySelector('[data-lightbox-prev]').click();};show();pauseVideos();dialog.showModal();document.body.classList.add('lightbox-open');dialog.querySelector('.lightbox-close').focus();}
async function sliders(signal){const elements=[...document.querySelectorAll('[data-studio-swiper]')];if(!elements.length)return;const {Swiper,Autoplay,Navigation,Pagination,A11y,Keyboard}=await import('./swiper.js');if(signal.aborted)return;for(const el of elements){const kind=el.dataset.swiperKind,isHero=kind==='hero';const count=el.querySelectorAll('.swiper-slide').length;let paused=false;const swiper=new Swiper(el,{modules:[Autoplay,Navigation,Pagination,A11y,Keyboard],slidesPerView:1,spaceBetween:kind==='reviews'?24:kind==='related'||kind==='partners'?18:0,autoHeight:kind==='gallery',loop:el.dataset.loop==='1'&&count>(kind==='partners'?4:1),rewind:kind==='partners'&&count>1&&count<=4,allowTouchMove:el.dataset.drag!=='0',speed:reduced()?0:550,autoplay:el.dataset.autoplay==='1'&&!reduced()&&count>1?{delay:Math.max(2500,Number(el.dataset.delay)||6500),disableOnInteraction:false,pauseOnMouseEnter:true}:false,navigation:{nextEl:el.querySelector('.swiper-next'),prevEl:el.querySelector('.swiper-prev')},pagination:{el:el.querySelector('.swiper-pagination'),clickable:true},keyboard:{enabled:true,onlyInViewport:true},a11y:{enabled:true,prevSlideMessage:document.documentElement.lang==='ar'?'السابق':'Previous slide',nextSlideMessage:document.documentElement.lang==='ar'?'التالي':'Next slide',paginationBulletMessage:document.documentElement.lang==='ar'?'اذهب للشريحة {{index}}':'Go to slide {{index}}'},breakpoints:kind==='reviews'?{768:{slidesPerView:2},1200:{slidesPerView:2}}:kind==='related'?{700:{slidesPerView:2,spaceBetween:20},1100:{slidesPerView:3,spaceBetween:24}}:kind==='partners'?{640:{slidesPerView:Math.min(2,Math.max(1,count-1))},1024:{slidesPerView:Math.min(4,count)}}:{}});instances.push(swiper);
const activeVideo=()=>{el.querySelectorAll('.swiper-slide').forEach(slide=>{if(isHero){const active=slide===swiper.slides[swiper.activeIndex];slide.inert=!active;slide.setAttribute('aria-hidden',String(!active));}});el.querySelectorAll('video').forEach(v=>{const slide=v.closest('.swiper-slide');const visible=v.getClientRects().length>0;if(slide===swiper.slides[swiper.activeIndex]&&visible&&isHero&&!paused&&!reduced()&&!document.hidden){v.muted=true;const source=v.querySelector('source[data-src]');if(source&&!source.getAttribute('src')){source.src=source.dataset.src;v.load();}const promise=v.play();promise?.catch(()=>swiper.autoplay?.start());if(slide.dataset.videoAdvance==='ended'){swiper.autoplay?.stop();v.onended=()=>{if(!paused)swiper.slideNext();};}else{v.loop=true;}}else{v.pause();v.onended=null;}});if(!el.querySelector('.swiper-slide-active video')&&!paused&&el.dataset.autoplay==='1'&&!reduced())swiper.autoplay?.start();el.querySelectorAll('[data-slide-to]').forEach(button=>button.classList.toggle('active',Number(button.dataset.slideTo)===swiper.realIndex));};swiper.on('slideChangeTransitionEnd',activeVideo);swiper.on('slideChangeTransitionStart',()=>el.querySelectorAll('video').forEach(v=>v.pause()));activeVideo();el.querySelector('.swiper-pause')?.addEventListener('click',e=>{paused=!paused;e.currentTarget.setAttribute('aria-pressed',String(paused));e.currentTarget.textContent=paused?'▶':'Ⅱ';paused?swiper.autoplay?.stop():swiper.autoplay?.start();activeVideo();},{signal});el.querySelectorAll('[data-slide-to]').forEach(button=>button.addEventListener('click',()=>swiper.slideTo(Number(button.dataset.slideTo)),{signal}));document.addEventListener('visibilitychange',()=>{if(document.hidden){el.querySelectorAll('video').forEach(v=>v.pause());swiper.autoplay?.stop();}else{activeVideo();if(!paused&&el.dataset.autoplay==='1')swiper.autoplay?.start();}},{signal});window.addEventListener('resize',activeVideo,{signal,passive:true});}}
function pageVideos(signal){
    document.querySelectorAll('[data-studio-video]').forEach(player=>{
        if(player.dataset.videoReady==='1')return;
        const video=player.querySelector('video');
        const toggles=[...player.querySelectorAll('[data-video-toggle]')];
        const progress=player.querySelector('[data-video-progress]');
        const time=player.querySelector('[data-video-time]');
        const durationLabel=player.querySelector('[data-video-duration]');
        const mute=player.querySelector('[data-video-mute]');
        const fullscreen=player.querySelector('[data-video-fullscreen]');
        const error=player.querySelector('[data-video-error]');
        if(!video)return;
        player.dataset.videoReady='1';

        const format=value=>{
            const seconds=Number.isFinite(value)?Math.max(0,Math.floor(value)):0;
            const hours=Math.floor(seconds/3600);
            const minutes=Math.floor((seconds%3600)/60);
            const rest=String(seconds%60).padStart(2,'0');
            return hours?hours+':'+String(minutes).padStart(2,'0')+':'+rest:minutes+':'+rest;
        };
        const ensureSource=()=>{
            const source=video.querySelector('source[data-src]');
            if(source&&!source.getAttribute('src')){
                source.src=source.dataset.src;
                source.removeAttribute('data-src');
                video.load();
            }
        };
        const syncPlay=()=>{
            const playing=!video.paused&&!video.ended;
            player.classList.toggle('is-playing',playing);
            toggles.forEach(button=>{
                button.setAttribute('aria-label',playing?player.dataset.pauseLabel:player.dataset.playLabel);
                button.setAttribute('aria-pressed',String(playing));
            });
        };
        const syncMute=()=>{
            const muted=video.muted||video.volume===0;
            player.classList.toggle('is-muted',muted);
            mute?.setAttribute('aria-label',muted?player.dataset.unmuteLabel:player.dataset.muteLabel);
            mute?.setAttribute('aria-pressed',String(muted));
        };
        const syncTime=()=>{
            const duration=Number.isFinite(video.duration)?video.duration:0;
            const value=duration?video.currentTime/duration*100:0;
            if(progress){
                progress.value=String(value);
                progress.style.setProperty('--video-progress',value+'%');
            }
            if(time)time.textContent=format(video.currentTime)+' / '+format(duration);
            if(durationLabel)durationLabel.textContent=format(duration);
        };
        const setBuffering=buffering=>player.classList.toggle('is-buffering',buffering);
        const play=()=>{
            ensureSource();
            document.querySelectorAll('[data-studio-video] video').forEach(other=>{
                if(other!==video)other.pause();
            });
            error?.setAttribute('hidden','');
            const promise=video.play();
            promise?.catch(()=>{
                setBuffering(false);
                syncPlay();
            });
        };
        const toggle=()=>{
            if(video.paused||video.ended){
                if(video.ended)video.currentTime=0;
                play();
            }else video.pause();
        };
        const seek=seconds=>{
            ensureSource();
            if(Number.isFinite(video.duration)){
                video.currentTime=Math.min(video.duration,Math.max(0,video.currentTime+seconds));
                syncTime();
            }
        };
        const toggleFullscreen=()=>{
            if(document.fullscreenElement){
                document.exitFullscreen?.();
            }else if(player.requestFullscreen){
                player.requestFullscreen()?.catch?.(()=>video.webkitEnterFullscreen?.());
            }else{
                video.webkitEnterFullscreen?.();
            }
        };

        toggles.forEach(button=>button.addEventListener('click',toggle,{signal}));
        video.addEventListener('click',toggle,{signal});
        video.addEventListener('dblclick',toggleFullscreen,{signal});
        video.addEventListener('play',syncPlay,{signal});
        video.addEventListener('playing',()=>setBuffering(false),{signal});
        video.addEventListener('pause',()=>{setBuffering(false);syncPlay();},{signal});
        video.addEventListener('ended',()=>{setBuffering(false);syncPlay();},{signal});
        video.addEventListener('waiting',()=>setBuffering(true),{signal});
        video.addEventListener('seeking',()=>setBuffering(true),{signal});
        video.addEventListener('seeked',()=>setBuffering(false),{signal});
        video.addEventListener('canplay',()=>setBuffering(false),{signal});
        video.addEventListener('loadedmetadata',syncTime,{signal});
        video.addEventListener('durationchange',syncTime,{signal});
        video.addEventListener('timeupdate',syncTime,{signal});
        video.addEventListener('volumechange',syncMute,{signal});
        video.addEventListener('error',()=>{
            setBuffering(false);
            error?.removeAttribute('hidden');
            syncPlay();
        },{signal});
        progress?.addEventListener('input',()=>{
            ensureSource();
            if(Number.isFinite(video.duration)){
                video.currentTime=Number(progress.value)/100*video.duration;
                syncTime();
            }
        },{signal});
        mute?.addEventListener('click',()=>{
            video.muted=!video.muted;
            syncMute();
        },{signal});
        fullscreen?.addEventListener('click',toggleFullscreen,{signal});
        document.addEventListener('fullscreenchange',()=>{
            const active=document.fullscreenElement===player;
            player.classList.toggle('is-fullscreen',active);
            fullscreen?.setAttribute('aria-label',active?player.dataset.exitFullscreenLabel:player.dataset.fullscreenLabel);
            fullscreen?.setAttribute('aria-pressed',String(active));
        },{signal});
        player.addEventListener('keydown',event=>{
            if(event.target!==player)return;
            const key=event.key.toLowerCase();
            if(event.key===' '||key==='k'){
                event.preventDefault();
                toggle();
            }else if(event.key==='ArrowLeft'){
                event.preventDefault();
                seek(-5);
            }else if(event.key==='ArrowRight'){
                event.preventDefault();
                seek(5);
            }else if(key==='m'){
                event.preventDefault();
                video.muted=!video.muted;
            }else if(key==='f'){
                event.preventDefault();
                toggleFullscreen();
            }
        },{signal});

        syncPlay();
        syncMute();
        syncTime();
    });
}
function visitorTracking(signal,config){
    if(!config.internal||!config.endpoint||navigator.globalPrivacyControl||navigator.doNotTrack==='1')return;
    const uuid=()=>crypto.randomUUID?.()||'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>{const r=Math.random()*16|0;return(c==='x'?r:r&3|8).toString(16);});
    let visitorId,sessionId;
    try{
        visitorId=sessionStorage.getItem('studio-anonymous-visitor')||uuid();
        sessionId=sessionStorage.getItem('studio-anonymous-session')||uuid();
        sessionStorage.setItem('studio-anonymous-visitor',visitorId);
        sessionStorage.setItem('studio-anonymous-session',sessionId);
    }catch{return;}
    const pageviewId=uuid(),started=Date.now();
    let maxScroll=0,formStarted=false,sentFinal=false;
    const params=new URLSearchParams(location.search);
    const base={visitor_id:visitorId,session_id:sessionId,pageview_id:pageviewId,path:location.pathname,title:document.title,referrer:document.referrer?new URL(document.referrer).origin:'',locale:document.documentElement.lang,language:navigator.language,viewport_width:innerWidth,viewport_height:innerHeight,utm_source:params.get('utm_source'),utm_medium:params.get('utm_medium'),utm_campaign:params.get('utm_campaign'),utm_content:params.get('utm_content'),utm_term:params.get('utm_term')};
    const send=(payload,beacon=false)=>{const body=JSON.stringify({...base,...payload,_token:config.csrf});if(beacon&&navigator.sendBeacon){navigator.sendBeacon(config.endpoint,new Blob([body],{type:'application/json'}));return;}fetch(config.endpoint,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':config.csrf,'Accept':'application/json'},body,credentials:'same-origin',keepalive:true}).catch(()=>{});};
    const activity=(final=false)=>{if(final&&sentFinal)return;sentFinal=sentFinal||final;send({type:'activity',duration:Math.min(86400,Math.round((Date.now()-started)/1000)),scroll_depth:maxScroll},final);};
    const event=(name,label,target)=>send({type:'event',event_id:uuid(),event:name,label:(label||'').trim().slice(0,255),target_path:target||null});
    send({type:'pageview'});
    const onScroll=()=>{const available=Math.max(1,document.documentElement.scrollHeight-innerHeight);maxScroll=Math.max(maxScroll,Math.min(100,Math.round(scrollY/available*100)));};
    window.addEventListener('scroll',onScroll,{signal,passive:true});
    const interval=setInterval(()=>activity(),15000);
    signal.addEventListener('abort',()=>{clearInterval(interval);activity(true);},{once:true});
    document.addEventListener('visibilitychange',()=>{if(document.hidden)activity();},{signal});
    window.addEventListener('pagehide',()=>activity(true),{signal,once:true});
    document.addEventListener('click',e=>{const target=e.target.closest('a,button');if(!target)return;const href=target instanceof HTMLAnchorElement?target.href:'';const label=(target.getAttribute('aria-label')||target.textContent||'').trim();if(/^https?:\/\/(wa\.me|api\.whatsapp\.com)/i.test(href)||/^whatsapp:/i.test(href))event('whatsapp_click',label);else if(/^https?:/i.test(href)&&new URL(href).origin!==location.origin)event('outbound_click',label,new URL(href).pathname);else if(target instanceof HTMLAnchorElement&&target.hasAttribute('download'))event('download',label,new URL(href,location.href).pathname);else if(target.matches('.button,[data-cta]'))event('cta_click',label,target instanceof HTMLAnchorElement?new URL(href,location.href).pathname:null);},{signal});
    document.addEventListener('focusin',e=>{if(!formStarted&&e.target.matches('form input,form select,form textarea')){formStarted=true;event('form_start',e.target.closest('form')?.getAttribute('aria-label')||'form');}},{signal});
    document.addEventListener('submit',e=>event('form_submit',e.target.getAttribute('aria-label')||'form'),{signal});
    document.querySelectorAll('video').forEach(video=>{video.addEventListener('play',()=>{if(video.dataset.analyticsPlayed!=='1'){video.dataset.analyticsPlayed='1';event('video_play',video.getAttribute('aria-label')||document.title);}},{signal});video.addEventListener('ended',()=>event('video_complete',video.getAttribute('aria-label')||document.title),{signal});});
}
function tracking(signal){
    const raw=document.getElementById('tracking-config');
    if(!raw)return;
    const config=JSON.parse(raw.textContent);
    visitorTracking(signal,config);
    if(!config.external)return;
    const banner=document.getElementById('tracking-consent');
    if(!banner)return;
    let choice;
    try{choice=localStorage.getItem('studio-consent');}catch{}
    const enableExternal=()=>{if(window.__studioExternalTracking)return;window.__studioExternalTracking=true;const script=(src)=>{const s=document.createElement('script');s.src=src;s.async=true;document.head.append(s);};if(/^G-[A-Z0-9]+$/.test(config.ga||'')){window.dataLayer=window.dataLayer||[];window.gtag=function(){window.dataLayer.push(arguments);};window.gtag('js',new Date());window.gtag('config',config.ga);script('https://www.googletagmanager.com/gtag/js?id='+config.ga);}if(/^[0-9]+$/.test(config.meta||'')){window.fbq=window.fbq||function(){window.fbq.callMethod?window.fbq.callMethod.apply(window.fbq,arguments):window.fbq.queue.push(arguments);};window.fbq.queue=[];window.fbq.loaded=true;window.fbq.version='2.0';script('https://connect.facebook.net/en_US/fbevents.js');window.fbq('init',config.meta);window.fbq('track','PageView');}if(/^[A-Za-z0-9_-]+$/.test(config.tiktok||'')){window.TiktokAnalyticsObject='ttq';window.ttq=window.ttq||[];window.ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'];window.ttq.methods.forEach(method=>window.ttq[method]=function(){window.ttq.push([method,...arguments]);});script('https://analytics.tiktok.com/i18n/pixel/events.js?sdkid='+config.tiktok+'&lib=ttq');window.ttq.page();}for(const [place,code]of [[document.head,config.head],[document.body,config.body]]){if(!code)continue;const temp=document.createElement('template');temp.innerHTML=code;for(const node of temp.content.childNodes){if(node.nodeName==='SCRIPT'){const s=document.createElement('script');for(const attr of node.attributes)s.setAttribute(attr.name,attr.value);s.textContent=node.textContent;place.append(s);}else place.append(node.cloneNode(true));}}};
    if(choice==='accept')enableExternal();else if(choice!=='decline')banner.hidden=false;
    banner.querySelectorAll('[data-consent]').forEach(button=>button.addEventListener('click',()=>{choice=button.dataset.consent;try{localStorage.setItem('studio-consent',choice);}catch{}banner.hidden=true;if(choice==='accept')enableExternal();},{signal}));
}
function initialize(){pageAbort?.abort();instances.forEach(s=>s.destroy(true,true));instances=[];observer?.disconnect();pageAbort=new AbortController();const signal=pageAbort.signal;hideLoader();requestAnimationFrame(()=>document.querySelector('.page-transition')?.classList.remove('is-visible'));document.body.classList.remove('menu-is-open');document.querySelector('main').inert=false;document.querySelector('footer').inert=false;document.documentElement.style.setProperty('--transition-duration',(Number(document.body.dataset.transitionDuration)||400)+'ms');revealElements();const header=document.getElementById('site-header'),hero=document.getElementById('home-hero');const updateNav=()=>header?.classList.toggle('is-solid',!hero||window.scrollY>32);updateNav();window.addEventListener('scroll',updateNav,{signal,passive:true});window.addEventListener('resize',updateNav,{signal,passive:true});const mobile=document.getElementById('mobile-nav'),toggle=document.querySelector('.menu-toggle');toggle?.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));mobile.hidden=!open;document.body.classList.toggle('menu-is-open',open);document.querySelector('main').inert=open;document.querySelector('footer').inert=open;if(open)mobile.querySelector('a')?.focus();},{signal});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!mobile?.hidden){mobile.hidden=true;toggle?.setAttribute('aria-expanded','false');document.body.classList.remove('menu-is-open');document.querySelector('main').inert=false;document.querySelector('footer').inert=false;toggle?.focus();}},{signal});document.querySelectorAll('.submenu-toggle').forEach(button=>button.addEventListener('click',()=>{const open=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(open));button.closest('li').classList.toggle('nav-open',open);},{signal}));document.querySelector('.theme-button')?.addEventListener('click',()=>{const theme=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=theme;try{localStorage.setItem('studio-theme',theme);}catch{}},{signal});document.querySelector('.lightbox-close')?.addEventListener('click',closeLightbox,{signal});document.getElementById('gallery-lightbox')?.addEventListener('cancel',()=>{pauseVideos();lastFocus?.focus();},{signal});document.addEventListener('click',e=>{const button=e.target.closest('[data-gallery]');if(button){e.preventDefault();try{openGallery(JSON.parse(button.dataset.gallery),button);}catch{}}},{signal});pageVideos(signal);sliders(signal).catch(()=>{});tracking(signal);}
document.addEventListener('livewire:init',()=>Livewire.hook('morphed',()=>requestAnimationFrame(()=>{revealElements();if(pageAbort)pageVideos(pageAbort.signal);})));
document.addEventListener('livewire:navigating',()=>{pauseVideos();closeLightbox();if(document.body.dataset.transitions==='1'&&!reduced()){document.querySelector('.page-transition')?.classList.add('is-visible');setTimeout(()=>document.querySelector('.page-transition')?.classList.remove('is-visible'),1500);}});
document.addEventListener('livewire:navigated',initialize);
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initialize,{once:true});else initialize();


