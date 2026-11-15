(function(){
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
  const html=document.documentElement;
  const RM=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FINE=matchMedia('(pointer: fine)').matches;
  const mqMob=matchMedia('(max-width: 760px)');
  const clamp=(v,a,b)=>Math.min(b,Math.max(a,v));
  const ease=t=>t<.5?2*t*t:1-Math.pow(-2*t+2,2)/2;
  const ls={get(k){try{return localStorage.getItem(k)}catch(e){return null}},set(k,v){try{localStorage.setItem(k,v)}catch(e){}},del(k){try{localStorage.removeItem(k)}catch(e){}}};
  if(!RM) html.classList.add('anim');
  const FC=window.fcData||{};


  /* Intro semáforo */
  const intro=$('#intro'); let seen=false;
  try{seen=sessionStorage.getItem('fc-intro')==='1'}catch(e){}
  function ready(){html.classList.add('ready')}
  if(RM||seen){ready()} else {
    intro.hidden=false; const L=$$('.light',intro); let ended=false;
    const end=()=>{if(ended)return;ended=true;intro.classList.add('gone');ready();try{sessionStorage.setItem('fc-intro','1')}catch(e){}setTimeout(()=>{intro.hidden=true},650)};
    [0,480,960].forEach((t,i)=>setTimeout(()=>{L.forEach(l=>l.classList.remove('on'));L[i].classList.add('on')},t));
    setTimeout(end,1650); $('#skip').addEventListener('click',end);
  }

  /* Scroll suave */
  let lenis=null;
  if(window.Lenis&&!RM){try{lenis=new Lenis({lerp:.11,smoothWheel:true})}catch(e){lenis=null}}
  function goTo(target){const el=typeof target==='string'?$(target):target;if(!el)return;
    if(lenis){lenis.scrollTo(el,{offset:-10})}else{el.scrollIntoView({behavior:RM?'auto':'smooth',block:'start'})}}
  document.addEventListener('click',e=>{const a=e.target.closest('a[href^="#"]');if(!a)return;const id=a.getAttribute('href');if(id.length<2)return;const el=$(id);if(!el)return;e.preventDefault();closeMenu();goTo(el)});

  /* Menú móvil */
  const menu=$('#menu'),menuBtn=$('#menuBtn');
  function closeMenu(){if(menu.hidden)return;menu.hidden=true;menuBtn.setAttribute('aria-expanded','false');if(lenis)lenis.start();menuBtn.focus({preventScroll:true})}
  menuBtn.addEventListener('click',()=>{menu.hidden=false;menuBtn.setAttribute('aria-expanded','true');if(lenis)lenis.stop();$('#menuClose').focus()});
  $('#menuClose').addEventListener('click',closeMenu);

  /* Elementos que mueve el scroll */
  const nav=$('#nav'),hero=$('#hero'),heroMedia=$('#heroMedia'),claim=$('#claim'),prog=$('#progress span');
  const mqTrack=$('#mqTrack'),marquee=$('#marquee');
  const howto=$('#como'),track=$('#track'),roadCar=$('#roadCar'),hCount=$('#hCount'),panels=$$('.panel'),NP=Math.max(1,panels.length);
  const kit=$('#kit'),stage=$('#stage'),pTermo=$('.p-termo'),pPack=$('.p-pack'),pPiezas=$('.p-piezas'),kitHint=$('#kitHint');
  const mbar=$('#mbar');
  let lastY=scrollY,vel=0,mx=0,mdir=1,mPaused=false,quoteVisible=false,menuOpen=false;
  if(marquee){marquee.addEventListener('pointerenter',()=>{mPaused=true});marquee.addEventListener('pointerleave',()=>{mPaused=false})}

  let selPiece=null;
  function setPieces(amt){
    const w=stage.clientWidth;
    stage.style.setProperty('--amt',amt.toFixed(3));
    pTermo.style.transform=`translate(${-amt*w*.36}px,${-amt*w*.06}px) rotate(${-amt*10}deg)`;
    pPiezas.style.transform=`translate(${amt*w*.12}px,${amt*w*.1}px) rotate(${amt*8}deg)`;
    pPack.style.transform=`translate(${-amt*w*.08}px,${amt*w*.04}px)`;
  }

  function frame(t){
    if(lenis)lenis.raf(t);
    const y=scrollY,dy=y-lastY;lastY=y;vel=vel*.85+dy*.15;
    const vh=innerHeight,mob=mqMob.matches;
    /* progreso */
    const max=document.documentElement.scrollHeight-vh;prog.style.transform=`scaleX(${max>0?y/max:0})`;
    /* nav */
    if(menu.hidden){if(y>140&&dy>2)nav.classList.add('hide');else if(dy<-2||y<140)nav.classList.remove('hide')}
    nav.classList.toggle('compact',y>60);
    /* hero */
    const hr=hero.getBoundingClientRect();
    if(!RM){const p=clamp(-hr.top/(hr.height*.9),0,1);heroMedia.style.transform=`scale(${1-p*.1})`;heroMedia.style.borderRadius=`${16+p*18}px`;claim.style.transform=`translateY(${-p*70}px)`}
    /* barra móvil */
    mbar.classList.toggle('show',mob&&hr.bottom<vh*.4&&!quoteVisible);
    /* marquee */
    if(Math.abs(dy)>.5)mdir=dy>0?1:-1;
    if(mqTrack&&!RM&&!mPaused){mx-=(.7+Math.min(Math.abs(vel),40)*.3)*mdir;const half=mqTrack.scrollWidth/2;if(half>0){if(mx<=-half)mx+=half;if(mx>0)mx-=half}mqTrack.style.transform=`translate3d(${mx}px,0,0)`}
    /* cómo funciona (escritorio) */
    if(howto&&!mob&&!RM){const r=howto.getBoundingClientRect();const total=howto.offsetHeight-vh;const p=clamp(-r.top/total,0,1);
      const dist=Math.max(0,track.scrollWidth-innerWidth);track.style.transform=`translate3d(${-p*dist}px,0,0)`;
      const road=roadCar.parentElement;roadCar.style.transform=`translateX(${p*(road.clientWidth-roadCar.offsetWidth)}px)`;
      const idx=Math.min(NP-1,Math.floor(p*NP*1.00001));const s=String(idx+1).padStart(2,'0');if(hCount.textContent!==s)hCount.textContent=s;
      panels.forEach((pn,i)=>pn.classList.toggle('active',i===idx));}
    /* kit (escritorio) */
    if(!mob&&!RM&&!selPiece){const r=kit.getBoundingClientRect();const total=kit.offsetHeight-vh;const p=clamp(-r.top/total,0,1);
      const a=ease(clamp((p-.08)/.32,0,1)),b=ease(clamp((p-.72)/.2,0,1));setPieces(a*(1-b));
      kitHint.textContent=p<.08?'Baja para verlo desarmado':(p<.72?'Toca un punto para ver la pieza':'Listo para salir a la calle')}
    /* cursor */
    moveCursor();
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);

  /* Kit: piezas */
  const PIECES=FC.piezas||{};
  function pickPiece(k){
    selPiece=(selPiece===k)?null:k;
    $$('.chip[data-piece]').forEach(c=>c.setAttribute('aria-pressed',String(c.dataset.piece===selPiece)));
    $$('.piece').forEach(p=>p.classList.toggle('sel',p.dataset.piece===selPiece));
    stage.classList.toggle('focus',!!selPiece);
    const d=selPiece?PIECES[selPiece]:null;
    $('#dTitle').textContent=d?d.t:'Toca una pieza para ver su detalle';
    $('#dText').textContent=d?d.d:'Cada pieza tiene su ficha. Los datos entre corchetes los completa First Coffee.';
    const ul=$('#dSpecs');ul.innerHTML='';if(d){d.s.forEach(s=>{const li=document.createElement('li');li.textContent=s;ul.appendChild(li)});ul.hidden=false}else ul.hidden=true;
    stage.classList.add('tween');setPieces(selPiece?1:0);setTimeout(()=>stage.classList.remove('tween'),750);
  }
  $$('[data-piece]').forEach(el=>{if(el.tagName==='BUTTON')el.addEventListener('click',()=>pickPiece(el.dataset.piece))});

  /* Cómo funciona: historias en móvil */
  let sIdx=0,sTimer=null,howVisible=false;
  const bars=$$('#bars span');
  function showStory(i){if(!howto)return;sIdx=(i+NP)%NP;panels.forEach((p,k)=>p.classList.toggle('active',k===sIdx));hCount.textContent=String(sIdx+1).padStart(2,'0');
    bars.forEach((b,k)=>{b.classList.remove('run','done');if(k<sIdx)b.classList.add('done')});void bars[sIdx].offsetWidth;
    if(!RM&&howVisible)bars[sIdx].classList.add('run');
    clearTimeout(sTimer);if(!RM&&howVisible&&mqMob.matches)sTimer=setTimeout(()=>showStory(sIdx+1),4000)}
  if(howto){$('#tapNext').addEventListener('click',()=>showStory(sIdx+1));$('#tapPrev').addEventListener('click',()=>showStory(sIdx-1))}
  if('IntersectionObserver' in window){
    new IntersectionObserver(es=>es.forEach(e=>{howVisible=e.isIntersecting;if(mqMob.matches){if(howVisible)showStory(sIdx);else clearTimeout(sTimer)}}),{threshold:.35}).observe(howto||document.body);
    new IntersectionObserver(es=>es.forEach(e=>{quoteVisible=e.isIntersecting}),{threshold:.15}).observe($('#cotizar'));
  }
  mqMob.addEventListener('change',()=>{track.style.transform='';clearTimeout(sTimer);if(!mqMob.matches){bars.forEach(b=>b.classList.remove('run','done'))}else showStory(0)});

  /* Reveal y contadores */
  if('IntersectionObserver' in window&&!RM){
    const io=new IntersectionObserver(es=>es.forEach(e=>{if(!e.isIntersecting)return;const el=e.target;el.classList.remove('pending');el.classList.add('in');io.unobserve(el);
      if(el.classList.contains('stats'))countUp(el)}),{rootMargin:'0px 0px -12% 0px'});
    $$('.rv').forEach(el=>{if(el.getBoundingClientRect().top>innerHeight){el.classList.add('pending');if(el.classList.contains('stats'))$$('[data-count]',el).forEach(b=>b.textContent=fmt(0,b.dataset.fmt));io.observe(el)}});
    const wm=$('#wordmark');
    const io2=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){wm.classList.remove('pending');io2.disconnect()}}),{threshold:.3});
    if(wm.getBoundingClientRect().top>innerHeight){wm.classList.add('pending');io2.observe(wm)}
  }
  function fmt(n,f){const s=Math.round(n).toLocaleString('es-CL');return f==='money'?'$'+s:(f==='plus'?s+'+':s)}
  function countUp(root){$$('[data-count]',root).forEach((b,i)=>{const to=+b.dataset.count,f=b.dataset.fmt,t0=performance.now()+i*180,d=1400;
    (function step(now){const p=clamp((now-t0)/d,0,1);b.textContent=fmt(to*ease(p),f);if(p<1)requestAnimationFrame(step)})(t0)})}

  /* Videos: lightbox */
  const lb=$('#lightbox');
  const lbMedia=$('#lbMedia'),lbScene=lbMedia?lbMedia.querySelector('.scene'):null;let lbVid=null;
  $$('.tile').forEach(t=>t.addEventListener('click',()=>{
    $('#lbCap').textContent=t.dataset.cap;const ln=$('#lbLink');if(ln&&t.dataset.url)ln.href=t.dataset.url;
    if(lbVid){lbVid.remove();lbVid=null}
    if(t.dataset.video){lbVid=document.createElement('video');lbVid.className='media-cover';lbVid.src=t.dataset.video;if(t.dataset.poster)lbVid.poster=t.dataset.poster;lbVid.controls=true;lbVid.playsInline=true;lbVid.autoplay=true;lbMedia.prepend(lbVid);if(lbScene)lbScene.hidden=true}
    else if(t.dataset.poster){lbVid=document.createElement('img');lbVid.className='media-cover';lbVid.src=t.dataset.poster;lbVid.alt='';lbMedia.prepend(lbVid);if(lbScene)lbScene.hidden=true}
    else if(lbScene)lbScene.hidden=false;
    if(lb.showModal){lb.showModal();if(lenis)lenis.stop()}else lb.setAttribute('open','')}));
  lb.addEventListener('close',()=>{if(lenis)lenis.start();if(lbVid&&lbVid.pause)lbVid.pause()});
  lb.addEventListener('click',e=>{if(e.target===lb)lb.close()});

  /* Cursor propio y botones magnéticos */
  const cur=$('#cursor'),curLab=$('#cursorLab');let cx=-100,cy=-100,tx=-100,ty=-100;
  const useCursor=FINE&&!RM;
  if(useCursor){html.classList.add('has-cursor');
    addEventListener('pointermove',e=>{tx=e.clientX;ty=e.clientY;cur.classList.remove('hidden');
      const el=e.target.closest('[data-cursor],a,button,summary,label.drop');
      const lab=el&&el.dataset.cursor?el.dataset.cursor:'';
      cur.classList.toggle('big',!!lab);if(curLab.textContent!==lab)curLab.textContent=lab;});
    document.addEventListener('pointerleave',()=>cur.classList.add('hidden'));
    $$('.magnet').forEach(b=>{b.addEventListener('pointermove',e=>{const r=b.getBoundingClientRect();b.style.transform=`translate(${(e.clientX-r.left-r.width/2)*.22}px,${(e.clientY-r.top-r.height/2)*.32}px)`});
      b.addEventListener('pointerleave',()=>{b.style.transform=''})});
  }
  function moveCursor(){if(!useCursor)return;cx+=(tx-cx)*.22;cy+=(ty-cy)*.22;cur.style.transform=`translate3d(${cx}px,${cy}px,0)`}

  /* FAQ: una abierta a la vez */
  $$('.faq details').forEach(d=>d.addEventListener('toggle',()=>{if(d.open)$$('.faq details').forEach(o=>{if(o!==d)o.open=false})}));

  /* Cotizador */
  const DEF={step:1,qty:'',logo:'',logoName:'',comments:'',name:'',phone:'',email:'',comuna:'',src:''};
  let Q=Object.assign({},DEF),logoData='',logoFile=null,touched={},sending=false;
  const saved=ls.get('fc-quote');
  if(saved){try{const s=JSON.parse(saved);if(s&&s.step>1&&s.step<5){Q=Object.assign({},DEF,s);Q.logoName='';$('#resume').hidden=false}}catch(e){}}
  const steps=$$('.qstep');
  function save(){if(Q.step>=5)ls.del('fc-quote');else ls.set('fc-quote',JSON.stringify(Q))}
  function setText(id,v,empty){const el=$(id);el.textContent=v||empty;el.classList.toggle('empty',!v)}
  function render(focus){
    steps.forEach(s=>{s.hidden=+s.dataset.step!==Q.step});
    const pct=Q.step>=5?1:(Q.step-1)/4;const road=$('#qcar').parentElement;
    $('#qcar').style.transform=`translateX(${pct*(road.clientWidth-46)}px)`;$('#qfill').style.width=(pct*100)+'%';
    $('#qLabel').textContent=Q.step>=5?'Enviada':`Paso ${Q.step} de 4`;
    $$('[data-qty]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.qty===Q.qty)));
    $$('[data-logo]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.logo===Q.logo)));
    $$('[data-src]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.src===Q.src)));
    $('#logoArea').hidden=Q.logo!=='si';
    $('#q-comments').value=Q.comments;['name','phone','email','comuna'].forEach(k=>{const i=$('#q-'+k);if(i.value!==Q[k])i.value=Q[k]});
    setText('#sQty',Q.qty,'Sin elegir');
    setText('#sLogo',Q.logo==='si'?(Q.logoName?'Con logo: '+Q.logoName:'Con logo (lo mandas después)'):(Q.logo==='no'?'Sin logo':''),'Sin elegir');
    setText('#sCom',Q.comments,'Sin comentarios');
    setText('#sCon',[Q.name,Q.phone,Q.email].filter(Boolean).join(' · '),'Falta completar');
    if(Q.step===4)buildReview();
    if(focus){const h=$('.qstep:not([hidden]) h3');if(h)h.focus({preventScroll:true});const r=$('#qcard').getBoundingClientRect();if(r.top<0||r.top>innerHeight*.5)goTo('#qcard')}
    save();
  }
  function go(n){Q.step=n;render(true)}
  $$('[data-qty]').forEach(b=>b.addEventListener('click',()=>{Q.qty=b.dataset.qty;render();setTimeout(()=>go(2),RM?0:380)}));
  $$('[data-logo]').forEach(b=>b.addEventListener('click',()=>{Q.logo=b.dataset.logo;render()}));
  $$('[data-src]').forEach(b=>b.addEventListener('click',()=>{Q.src=Q.src===b.dataset.src?'':b.dataset.src;render()}));
  $$('[data-back]').forEach(b=>b.addEventListener('click',()=>go(Math.max(1,Q.step-1))));
  $('#q-comments').addEventListener('input',e=>{Q.comments=e.target.value.trim();setText('#sCom',Q.comments,'Sin comentarios');save()});
  $('[data-next="2"]').addEventListener('click',()=>{
    if(!Q.logo){$('#logoErr2').textContent='Elige si quieres tu logo o no.';return}
    $('#logoErr2').textContent='';go(3)});
  const V={
    name:v=>v.trim().length>=2?'':'Escribe tu nombre.',
    phone:v=>{let d=v.replace(/\D/g,'');if(d.startsWith('56'))d=d.slice(2);return /^9\d{8}$/.test(d)?'':'Escribe un celular de 9 dígitos que parta en 9, por ejemplo 9 1234 5678.'},
    email:v=>/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())?'':'Revisa el correo: le falta la @ o el dominio (por ejemplo, .cl).',
    comuna:v=>v.trim().length>=2?'':'Escribe tu comuna o ciudad.'
  };
  function check(k){const v=$('#q-'+k).value,msg=V[k](v),f=$(`.field[data-f="${k}"]`);$('#e-'+k).textContent=msg;f.classList.toggle('bad',!!msg);f.classList.toggle('ok',!msg);return !msg}
  Object.keys(V).forEach(k=>{const i=$('#q-'+k);
    i.addEventListener('input',()=>{Q[k]=i.value.trim();if(touched[k])check(k);setText('#sCon',[Q.name,Q.phone,Q.email].filter(Boolean).join(' · '),'Falta completar');save()});
    i.addEventListener('blur',()=>{if(i.value){touched[k]=true;check(k)}})});
  $('[data-next="3"]').addEventListener('click',()=>{let ok=true,first=null;Object.keys(V).forEach(k=>{touched[k]=true;if(!check(k)){ok=false;first=first||k}});if(ok)go(4);else $('#q-'+first).focus()});
  function buildReview(){
    const rows=[['Kits',Q.qty,1],['Logo',Q.logo==='si'?(Q.logoName?'Con logo: '+Q.logoName:'Con logo (lo mando después)'):'Sin logo',2],['Comentarios',Q.comments||'Sin comentarios',2],['Nombre',Q.name,3],['WhatsApp',Q.phone,3],['Correo',Q.email,3],['Comuna',Q.comuna,3],['Nos conociste por',Q.src||'Sin respuesta',3]];
    const dl=$('#review');dl.innerHTML='';
    rows.forEach(([k,v,s])=>{const d=document.createElement('div');const dt=document.createElement('dt');dt.textContent=k;const dd=document.createElement('dd');dd.textContent=v;const b=document.createElement('button');b.type='button';b.className='linkbtn';b.textContent='Editar';b.setAttribute('aria-label','Editar '+k.toLowerCase());b.addEventListener('click',()=>go(s));d.append(dt,dd,b);dl.appendChild(d)});
  }
  $('#qcard').addEventListener('submit',e=>e.preventDefault());
  $('#send').addEventListener('click',async()=>{
    if(sending)return;const btn=$('#send'),err=$('#sendErr');err.textContent='';
    const C=FC.cotizar;if(!C){err.textContent='El cotizador no está disponible ahora. Escríbenos por WhatsApp.';return}
    const fd=new FormData();
    fd.append('fc_nonce',C.nonce);fd.append('inicio',String(startedAt));fd.append('sitio_web',$('#q-web').value);
    fd.append('kits',Q.qty);fd.append('logo',Q.logo);fd.append('comentarios',Q.comments);
    fd.append('nombre',Q.name);fd.append('whatsapp',Q.phone);fd.append('correo',Q.email);fd.append('comuna',Q.comuna);fd.append('origen',Q.src);
    if(Q.logo==='si'&&logoFile)fd.append('archivo',logoFile,logoFile.name);
    sending=true;btn.disabled=true;const txt=btn.textContent;btn.textContent='Enviando…';
    try{
      const r=await fetch(C.endpoint,{method:'POST',body:fd,credentials:'same-origin'});
      let j={};try{j=await r.json()}catch(x){}
      if(!r.ok||!j.ok){
        err.textContent=(j&&j.mensaje)||'No pudimos enviar tu cotización. Inténtalo de nuevo o escríbenos por WhatsApp.';
        const map={name:3,phone:3,email:3,comuna:3,kits:1,logo:2};
        if(j&&j.campo&&map[j.campo]&&map[j.campo]!==4){go(map[j.campo]);const f=$(`.field[data-f="${j.campo}"]`);if(f){f.classList.add('bad');const e2=$('#e-'+j.campo);if(e2)e2.textContent=j.mensaje}else if(j.campo==='logo'){$('#logoErr2').textContent=j.mensaje}}
        return;
      }
      $('#doneNum').textContent=j.numero?`Número de solicitud: ${j.numero} · ${j.fecha||''}`:'';
      go(5);const d=$('#done');d.classList.remove('fill');void d.offsetWidth;setTimeout(()=>d.classList.add('fill'),120);
    }catch(x){err.textContent='Parece que no hay conexión. Revisa tu internet e inténtalo de nuevo.'}
    finally{sending=false;btn.disabled=false;btn.textContent=txt}
  });
  function reset(){Q=Object.assign({},DEF);touched={};clearLogo();$$('.field').forEach(f=>f.classList.remove('ok','bad'));$$('.field .err').forEach(e=>e.textContent='');$('#resume').hidden=true;ls.del('fc-quote');go(1)}
  $('#again').addEventListener('click',reset);$('#restart').addEventListener('click',reset);

  /* Logo: subir, previsualizar y compartir con el cotizador */
  const OK_TYPES=['image/png','image/jpeg','image/webp','application/pdf'];
  const startedAt=Date.now();
  function showLogo(){
    [$('#logoOn'),$('#logoOn2')].forEach((box,i)=>{box.innerHTML='';
      if(logoData){const img=document.createElement('img');img.src=logoData;img.alt='';box.appendChild(img)}
      else if(Q.logoName){const s=document.createElement('span');s.className='ph';s.textContent='PDF adjunto';box.appendChild(s)}
      else if(i===0){const s=document.createElement('span');s.className='ph';s.innerHTML='Tu logo<br>aquí';box.appendChild(s)}});
    const name=Q.logoName;
    $('#drop1Text').textContent=name?'Logo cargado: '+name:'Arrastra tu logo aquí o toca para elegirlo';
    $('#drop2Text').textContent=name?'Logo cargado: '+name:'Arrastra tu logo o toca para elegirlo';
    $('#logoClear').hidden=!name;
  }
  function clearLogo(){logoData='';logoFile=null;Q.logoName='';showLogo();save()}
  function handleFile(file,errEl){
    errEl.textContent='';if(!file)return;
    if(!OK_TYPES.includes(file.type)){errEl.textContent='Ese formato no sirve. Sube un PNG, JPG o PDF.';return}
    if(file.size>10*1024*1024){errEl.textContent='El archivo pesa más de 10 MB. Prueba con una versión más liviana.';return}
    logoFile=file;Q.logoName=file.name;Q.logo='si';
    if(file.type==='application/pdf'){logoData='';showLogo();render();return}
    const r=new FileReader();r.onload=()=>{logoData=r.result;showLogo();render()};r.onerror=()=>{errEl.textContent='No pudimos leer el archivo. Intenta de nuevo.'};r.readAsDataURL(file);
  }
  [['#drop1','#logoFile1','#logoErr1'],['#drop2','#logoFile2','#logoErr2']].forEach(([d,i,e])=>{
    const drop=$(d),inp=$(i),err=$(e);
    inp.addEventListener('change',()=>{handleFile(inp.files[0],err);inp.value=''});
    ['dragenter','dragover'].forEach(ev=>drop.addEventListener(ev,x=>{x.preventDefault();drop.classList.add('over')}));
    ['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,x=>{x.preventDefault();drop.classList.remove('over')}));
    drop.addEventListener('drop',x=>handleFile(x.dataTransfer.files[0],err));
  });
  $('#logoClear').addEventListener('click',()=>{clearLogo();render()});
  $('#logoToQuote').addEventListener('click',()=>{if(Q.logoName)Q.logo='si';Q.step=Q.qty?2:1;render();goTo('#cotizar')});
  $$('.sw').forEach(s=>s.addEventListener('click',()=>{$$('.sw').forEach(x=>x.setAttribute('aria-pressed',String(x===s)));html.style.setProperty('--pack',s.dataset.color)}));

  /* Mochila que gira con el scroll */
  const packView=$('#packView');
  if(!RM)addEventListener('scroll',()=>{const r=packView.getBoundingClientRect();const p=clamp(1-(r.top+r.height/2)/innerHeight,0,1);packView.style.transform=`rotate(${(.5-p)*-10}deg) scale(${.94+Math.min(p,.5)*.12})`},{passive:true});

  showLogo();render(false);
  if(mqMob.matches)showStory(0);
  addEventListener('resize',()=>render(false));
})();
