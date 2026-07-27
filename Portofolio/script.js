// ===== Loader =====
window.addEventListener('load', () => {
  setTimeout(() => {
    const loader = document.getElementById('loader');
    if (loader) loader.classList.add('hidden');
  }, 1200);
});

// ===== Custom Cursor =====
const cursor = document.getElementById('cursor');
const follower = document.getElementById('follower');
let mouseX = 0, mouseY = 0;
let followerX = 0, followerY = 0;

if (cursor && follower) {
  document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    cursor.style.left = mouseX - 4 + 'px';
    cursor.style.top = mouseY - 4 + 'px';
  });

  function animateFollower() {
    followerX += (mouseX - followerX) * 0.1;
    followerY += (mouseY - followerY) * 0.1;
    follower.style.left = followerX + 'px';
    follower.style.top = followerY + 'px';
    requestAnimationFrame(animateFollower);
  }
  animateFollower();

  const hoverTargets = document.querySelectorAll('a, button, .contact-item, .tag, .skill-group, .approach-card, .qa-step, .tool-chip, .design-card, .carousel-card, .carousel-arrow, .carousel-dot');
  hoverTargets.forEach(el => {
    el.addEventListener('mouseenter', () => follower.classList.add('hovering'));
    el.addEventListener('mouseleave', () => follower.classList.remove('hovering'));
  });
}

// ===== Nav Scroll =====
const nav = document.getElementById('nav');
if (nav) {
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 50);
  });
}

// ===== Nav Dark Mode on Work Section =====
const workSection = document.getElementById('work');
if (nav && workSection) {
  const navObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        nav.classList.add('nav--dark');
      } else {
        nav.classList.remove('nav--dark');
      }
    });
  }, { threshold: 0, rootMargin: '-80px 0px 0px 0px' });
  navObserver.observe(workSection);
}

// ===== Mobile Menu =====
const navToggle = document.getElementById('navToggle');
const mobileMenu = document.getElementById('mobileMenu');
let menuOpen = false;

if (navToggle && mobileMenu) {
  navToggle.addEventListener('click', () => {
    menuOpen = !menuOpen;
    mobileMenu.classList.toggle('active', menuOpen);
    document.body.style.overflow = menuOpen ? 'hidden' : '';
  });

  document.querySelectorAll('.mobile-link').forEach(link => {
    link.addEventListener('click', () => {
      menuOpen = false;
      mobileMenu.classList.remove('active');
      document.body.style.overflow = '';
    });
  });
}

// ===== Active Nav Link =====
const sections = document.querySelectorAll('.section, .hero');
const navLinks = document.querySelectorAll('.nav-link');

if (navLinks.length) {
  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const top = section.offsetTop - 200;
      if (window.scrollY >= top) {
        current = section.getAttribute('id');
      }
    });
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${current}`) {
        link.classList.add('active');
      }
    });
  });
}

// ===== Counter Animation (removed — replaced by visitor counter) =====

// ===== Reveal on Scroll =====
const reveals = document.querySelectorAll('[data-reveal]');
const revealObs = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('revealed');
      revealObs.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

reveals.forEach(el => revealObs.observe(el));

// ===== Smooth Scroll =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ===== Text Scramble =====
class TextScramble {
  constructor(el) {
    this.el = el;
    this.chars = '!<>-_\\/[]{}—=+*^?#________';
    this.update = this.update.bind(this);
  }

  setText(newText) {
    const oldText = this.el.innerText;
    const length = Math.max(oldText.length, newText.length);
    const promise = new Promise((resolve) => this.resolve = resolve);
    this.queue = [];
    for (let i = 0; i < length; i++) {
      const from = oldText[i] || '';
      const to = newText[i] || '';
      const start = Math.floor(Math.random() * 40);
      const end = start + Math.floor(Math.random() * 40);
      this.queue.push({ from, to, start, end });
    }
    cancelAnimationFrame(this.frameRequest);
    this.frame = 0;
    this.update();
    return promise;
  }

  update() {
    let output = '';
    let complete = 0;
    for (let i = 0, n = this.queue.length; i < n; i++) {
      let { from, to, start, end, char } = this.queue[i];
      if (this.frame >= end) {
        complete++;
        output += to;
      } else if (this.frame >= start) {
        if (!char || Math.random() < 0.28) {
          char = this.chars[Math.floor(Math.random() * this.chars.length)];
          this.queue[i].char = char;
        }
        output += `<span style="color:var(--cyan)">${char}</span>`;
      } else {
        output += from;
      }
    }
    this.el.innerHTML = output;
    if (complete === this.queue.length) {
      this.resolve();
    } else {
      this.frameRequest = requestAnimationFrame(this.update);
      this.frame++;
    }
  }
}

document.querySelectorAll('.nav-link').forEach(link => {
  const originalText = link.textContent;
  link.addEventListener('mouseenter', () => {
    const scrambler = new TextScramble(link);
    scrambler.setText(originalText);
  });
  link.addEventListener('mouseleave', () => {
    link.textContent = originalText;
  });
});

// ===== Magnetic Button =====
const magneticBtns = document.querySelectorAll('.btn, .nav-cta');
magneticBtns.forEach(btn => {
  btn.addEventListener('mousemove', (e) => {
    const rect = btn.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;
    btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = 'translate(0, 0)';
  });
});

// ===== Parallax =====
window.addEventListener('scroll', () => {
  const scrolled = window.scrollY;
  const heroRight = document.querySelector('.hero-right');
  if (heroRight) {
    heroRight.style.transform = `translateY(${scrolled * 0.06}px)`;
  }
  // Move blobs slightly
  document.querySelectorAll('.blob').forEach((blob, i) => {
    const speed = (i + 1) * 0.02;
    blob.style.transform = `translateY(${scrolled * speed}px)`;
  });
});

// ===== Tilt on Cards =====

// ===== Ripple Click =====
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
  @keyframes rippleOut {
    to { transform: scale(40); opacity: 0; }
  }
`;
document.head.appendChild(rippleStyle);

document.querySelectorAll('.contact-item').forEach(item => {
  item.addEventListener('click', function(e) {
    const ripple = document.createElement('span');
    const colors = ['var(--cyan)', 'var(--purple)', '#a855f7'];
    const color = colors[Math.floor(Math.random() * colors.length)];
    ripple.style.cssText = `
      position: absolute;
      width: 10px; height: 10px;
      background: ${color};
      border-radius: 50%;
      opacity: 0.3;
      left: ${e.offsetX}px;
      top: ${e.offsetY}px;
      transform: scale(0);
      animation: rippleOut 0.6s ease-out forwards;
      pointer-events: none;
    `;
    this.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  });
});

// ===== Scroll Progress Bar =====
const progressBar = document.createElement('div');
progressBar.style.cssText = `
  position: fixed;
  top: 0; left: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--cyan), var(--purple));
  z-index: 10001;
  transition: width 0.1s;
  width: 0%;
`;
document.body.appendChild(progressBar);

window.addEventListener('scroll', () => {
  const scrollTop = window.scrollY;
  const docHeight = document.documentElement.scrollHeight - window.innerHeight;
  const progress = (scrollTop / docHeight) * 100;
  progressBar.style.width = progress + '%';
});

// ===== Stagger Reveal on Skill Tags =====
const tagObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const tags = entry.target.querySelectorAll('.tag');
      tags.forEach((tag, i) => {
        tag.style.opacity = '0';
        tag.style.transform = 'translateY(10px)';
        setTimeout(() => {
          tag.style.transition = 'opacity 0.4s, transform 0.4s';
          tag.style.opacity = '1';
          tag.style.transform = 'translateY(0)';
        }, i * 60);
      });
      tagObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.3 });

document.querySelectorAll('.skill-tags').forEach(el => tagObserver.observe(el));

// ===== Skill Bar Fill Animation =====
const barObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const fill = entry.target.querySelector('.skill-bar-fill');
      if (fill) {
        const pct = fill.dataset.pct || 0;
        setTimeout(() => { fill.style.width = pct + '%'; }, 200);
      }
      barObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.3 });

document.querySelectorAll('.skill-bar').forEach(el => barObserver.observe(el));

// ===== Language Toggle =====
const translations = {
  id: {
    'nav.home': 'Beranda',
    'nav.skills': 'Skill',
    'nav.work': 'Proyek',
    'nav.facts': 'Fakta',
    'nav.contact': 'Kontak',
    'nav.cta': 'Hubungi Saya',
    'hero.status': 'OPEN FOR PROJECTS',
    'hero.sub': 'Developer · QA Engineer · Jakarta, ID',
    'hero.desc1': 'Gue Rizki Tri Wahyudi, Project Manager & QA Engineer yang passionate dengan pengalaman luas di Frontend development. Berdomisili di Indonesia.',
    'hero.desc2': 'Sejak 2020, gue seneng banget ngehadepin tantangan teknis yang kompleks, ngubah jadi solusi yang efisien, reliable, dan user-friendly. Kalau lagi gak manage project atau test system, biasanya gue lagi explore teknologi baru, optimasi workflow, atau nikmatin secangkir kopi.',
    'hero.btnWork': 'Lihat Proyek Gue',
    'visitor.title': 'Visitor saat ini:',
    'visitor.orang': 'orang',
    'skills.title': 'Skill Gue.',
    'skills.desc': 'Bahasa, framework, dan tools yang gue pakai buat ngebangun produk dari nol sampai produksi.',
    'skillbar.pm': 'Manajemen Proyek',
    'skillbar.qa': 'Quality Assurance',
    'skillbar.frontend': 'Frontend Development',
    'skillbar.testing': 'Manual Testing',
    'skillbar.leadership': 'Kepemimpinan Tim',
    'skillbar.integration': 'Integrasi Sistem',
    'skillgrp.frontend': 'Frontend Development',
    'skillgrp.qa': 'QA & Testing',
    'skillgrp.uiux': 'UI/UX Design',
    'skillgrp.iot': 'IoT & Hardware',
    'skillgrp.pm': 'Manajemen Proyek',
    'skillgrp.platforms': 'Platform',
    'work.title': 'Proyek pilihan.',
    'work.desc': 'Beberapa proyek yang udah gue garap, dari website organisasi sampe dashboard IoT.',
    'facts.title': 'Fakta Random.',
    'facts.photo': 'Foto lo di sini',
    'fact1': 'Gue sedikit kecanduan optimasi workflow dan proses.',
    'fact2': 'Suka banget baca blog teknologi dan metodologi project management.',
    'fact3': 'Gue enjoy ngebangun sistem yang reliable, entah itu kode atau struktur project.',
    'fact4': 'Agile methodology itu pendekatan favorit gue (di kerja dan hidup).',
    'fact5': 'Secangkir kopi yang bagus jadi bahan bakar sesi problem-solving terbaik gue.',
    'fact6': 'Masih cariin tool project management yang sempurna.',
    'contact.title': 'Punya ide? Ngobrol yuk.',
    'contact.desc': 'Lagi cari developer buat proyek, kolaborasi, atau sekadar mau nanya-nanya — inbox gue selalu kebuka.',
    'project.view': 'Lihat Detail',
    'project.live': 'Live Demo',
    'sub.back': '&larr; Kembali ke Work',
    'dfarm.metaRole': 'Peran',
    'dfarm.metaDate': 'Tanggal',
    'dfarm.metaDateVal': 'Oktober 2024',
    'dfarm.hero': 'Sistem manajemen dan quality assurance komprehensif untuk ERP poultry berbasis IoT. Mengubah platform multi-modul yang kompleks menjadi sistem yang andal dan mudah digunakan melalui koordinasi proyek yang terstruktur dan protokol pengujian yang ketat.',
    'dfarm.s1': 'Kenapa gue manage proyek ini',
    'dfarm.s1p1': 'Selama terlibat di D\'Farm Dream — peternakan telur modern pertama di Indonesia yang pakai sistem IoT — gue ambil peran sebagai Project Manager sekaligus QA Manual. Proyek ini butuh koordinasi beberapa tim, manajemen timeline yang kompleks, dan pengujian menyeluruh untuk platform pertanian yang mengintegrasikan workflow karyawan, manajemen kandang & gudang, serta monitoring keuangan dan investor.',
    'dfarm.s1p2': 'Fokus gue adalah mengefisienkan komunikasi tim, menetapkan milestone yang jelas, dan melakukan manual testing intensif untuk menemukan serta menyelesaikan masalah sistem sebelum deployment.',
    'dfarm.s2': 'Project Management Approach',
    'dfarm.s2c1': 'Team Coordination',
    'dfarm.s2c1p': 'Memimpin daily standup, sprint planning, dan retrospectives untuk memastikan kolaborasi yang lancar antara tim development, desain, dan testing.',
    'dfarm.s2c2': 'Timeline Management',
    'dfarm.s2c2p': 'Membuat dan memelihara jadwal proyek, mengidentifikasi dependensi critical path, dan mengelola ekspektasi stakeholder selama siklus development.',
    'dfarm.s2c3': 'Risk Mitigation',
    'dfarm.s2c3p': 'Mengidentifikasi potensi risiko secara proaktif, membuat rencana kontinjensi, dan memastikan deliverable proyek memenuhi standar kualitas dan deadline.',
    'dfarm.s2c4': 'Quality Assurance',
    'dfarm.s2c4p': 'Menetapkan protokol pengujian komprehensif, melakukan sesi manual testing, dan memelihara proses pelacakan serta resolusi bug yang terstruktur.',
    'dfarm.s3': 'Quality Assurance Methodology',
    'dfarm.s3s1': 'Test Planning',
    'dfarm.s3s1p': 'Membuat test plan komprehensif yang mencakup pengujian fungsional, integrasi, dan user acceptance testing untuk semua modul sistem.',
    'dfarm.s3s2': 'Manual Testing',
    'dfarm.s3s2p': 'Menjalankan pengujian manual sistematis di platform web dan mobile, fokus pada workflow pengguna, integritas data, dan performa sistem.',
    'dfarm.s3s3': 'Bug Tracking',
    'dfarm.s3s3p': 'Mendokumentasikan dan memprioritaskan isu menggunakan laporan bug terstruktur, berkoordinasi dengan tim development untuk resolusi tepat waktu.',
    'dfarm.s3s4': 'Validation',
    'dfarm.s3s4p': 'Melakukan regression testing dan validasi akhir untuk memastikan semua fitur berfungsi dengan benar sebelum deployment ke production.',
    'dfarm.s4': 'Core Visual Elements',
    'dfarm.s4p': 'Visual language yang konsisten untuk pengalaman pengguna yang cohesive. Berikut adalah elemen dasar yang menjadi panduan selama proses quality assurance.',
    'dfarm.s4c1': 'Primary Color Palette',
    'dfarm.s4c2': 'Typography Scale',
    'dfarm.s5': 'Designing with Clarity',
    'dfarm.s5p': 'Mockup real-world yang menunjukkan bagaimana UI final terlihat di device sungguhan. Selama QA, gue fokus memastikan interface terasa familiar dan mudah digunakan dari genggaman tangan pengguna.',
    'dfarm.s6': 'Managed with Precision, Tested with Care',
    'dfarm.s6p1': 'Pendekatan project management dan quality assurance ini dibuat dengan niat dan presisi, berangkat dari metodologi yang udah gue rafinasi dari pengalaman mengoordinasi proyek teknis yang kompleks. Setiap detail — dari sprint planning sampai alur pelacakan bug — dirancang untuk memastikan delivery yang sukses dengan standar kualitas tertinggi.',
    'dfarm.s6p2': 'Proses pengujian menyeluruh dan koordinasi proyek yang dikembangkan untuk D\'Farm Dream terus menjadi fondasi pendekatan gue terhadap proyek teknis yang kompleks.',
    'figma.hero': 'Desain antarmuka mobile dan web application dengan fokus pada user experience. Dari wireframing sampai prototyping — setiap elemen dirancang untuk memudahkan pengguna mencapai tujuan mereka.',
    'figma.metaRole': 'Peran',
    'figma.metaTools': 'Tools',
    'figma.s1': 'Design Process',
    'figma.s1p': 'Setiap project desain dimulai dari pemahaman kebutuhan pengguna, lalu diterjemahkan menjadi antarmuka yang intuitif dan visually appealing.',
    'figma.s1r1': 'Research',
    'figma.s1r1p': 'Analisis kebutuhan pengguna, kompetitor, dan tren desain yang relevan.',
    'figma.s1r2': 'Wireframe',
    'figma.s1r2p': 'Sketsa layout dan struktur informasi sebelum masuk ke desain visual.',
    'figma.s1r3': 'Visual Design',
    'figma.s1r3p': 'Menentukan color palette, typography, dan elemen visual yang cohesive.',
    'figma.s1r4': 'Prototype',
    'figma.s1r4p': 'Membuat prototype interaktif untuk testing alur pengguna sebelum development.',
    'figma.s2': 'Selected Works',
    'figma.s2p': 'Beberapa desain yang udah gue kerjakan, mulai dari mobile app sampai dashboard web.',
    'figma.s2c1': 'SmartHome Control',
    'figma.s2c1p': 'Desain mobile app untuk monitoring dan kontrol perangkat IoT rumah pintar. Fokus pada real-time data dan kemudahan akses.',
    'figma.s2c2': 'YPOK Admin Dashboard',
    'figma.s2c2p': 'Interface admin untuk mengelola data anggota karate, verifikasi ijazah, dan pencarian direktori. Clean dan functional.',
    'figma.s2c3': 'Lebak Ciherang Website',
    'figma.s2c3p': 'Website wisata edukasi dengan booking integrasi. Desain yang mengundang dengan navigasi intuitif untuk pengunjung.',
    'figma.s2c4': 'D\'Farm Dream ERP',
    'figma.s2c4p': 'Mobile interface untuk sistem ERP peternakan. Monitoring kandang, stok pakan, dan data produksi dalam genggaman.',
    'figma.s3': 'Tools & Skills',
    'figma.s3p': 'Tools yang gue pakai buat translate ide jadi desain yang bisa di-develop.',
    'figma.s4': 'Designed with Users in Mind',
    'figma.s4p1': 'Setiap desain yang gue buat bermula dari pertanyaan sederhana: "gimana cara bikin pengguna lebih gampang?" Dari situ, gue build wireframe, iterasi visual, sampai jadi prototype yang bisa langsung diuji.',
    'figma.s4p2': 'Gue percaya desain yang bagus itu bukan cuma soal tampilan cantik — tapi soal gimana interface bisa ngomong ke pengguna tanpa perlu banyak penjelasan.'
  },
  en: {
    'nav.home': 'Home',
    'nav.skills': 'Skills',
    'nav.work': 'Work',
    'nav.facts': 'Facts',
    'nav.contact': 'Contact',
    'nav.cta': 'Contact Me',
    'hero.status': 'OPEN FOR PROJECTS',
    'hero.sub': 'Developer · QA Engineer · Jakarta, ID',
    'hero.desc1': "I'm Rizki Tri Wahyudi, a passionate Project Manager and Quality Assurance Engineer with extensive experience in Frontend development. Based in Indonesia.",
    'hero.desc2': "Since 2020, I've enjoyed tackling complex technical challenges, transforming them into efficient, reliable, and user-friendly solutions. When I'm not managing projects or testing systems, you'll find me exploring new technologies, optimizing workflows, or simply enjoying a good cup of coffee.",
    'hero.btnWork': 'View My Work',
    'visitor.title': 'Visitors now:',
    'visitor.orang': 'people',
    'skills.title': 'My Skills.',
    'skills.desc': 'Languages, frameworks, and tools I use to build products from scratch to production.',
    'skillbar.pm': 'Project Management',
    'skillbar.qa': 'Quality Assurance',
    'skillbar.frontend': 'Frontend Development',
    'skillbar.testing': 'Manual Testing',
    'skillbar.leadership': 'Team Leadership',
    'skillbar.integration': 'System Integration',
    'skillgrp.frontend': 'Frontend Development',
    'skillgrp.qa': 'QA & Testing',
    'skillgrp.uiux': 'UI/UX Design',
    'skillgrp.iot': 'IoT & Hardware',
    'skillgrp.pm': 'Project Management',
    'skillgrp.platforms': 'Platforms',
    'work.title': 'Featured Projects.',
    'work.desc': 'Some projects I have worked on, from organization websites to IoT dashboards.',
    'facts.title': 'Random Facts.',
    'facts.photo': 'Your photo here',
    'fact1': "I'm slightly addicted to optimizing workflows and processes.",
    'fact2': 'Love exploring tech blogs and project management methodologies.',
    'fact3': "I enjoy building reliable systems, whether it's code or project structures.",
    'fact4': 'Agile methodology is my go-to approach (in work and life).',
    'fact5': 'A good cup of coffee fuels my best problem-solving sessions.',
    'fact6': 'Still searching for the perfect project management tool.',
    'contact.title': "Got an idea? Let's talk.",
    'contact.desc': 'Looking for a developer for a project, collaboration, or just want to ask questions — my inbox is always open.',
    'project.view': 'View Details',
    'project.live': 'Live Demo',
    'sub.back': '&larr; Back to Work',
    'dfarm.metaRole': 'Role',
    'dfarm.metaDate': 'Date',
    'dfarm.metaDateVal': 'October 2024',
    'dfarm.hero': 'Comprehensive management and quality assurance system for IoT-based poultry ERP. Transforming a complex multi-modular platform into a reliable and easy-to-use system through structured project coordination and rigorous testing protocols.',
    'dfarm.s1': 'Why I managed this project',
    'dfarm.s1p1': 'During my involvement in D\'Farm Dream — Indonesia\'s first modern egg farm using IoT systems — I took on the role of Project Manager and QA Manual. This project required coordinating multiple teams, complex timeline management, and comprehensive testing for an agricultural platform integrating employee workflows, coop & warehouse management, as well as financial and investor monitoring.',
    'dfarm.s1p2': 'My focus was on streamlining team communication, setting clear milestones, and conducting intensive manual testing to find and resolve system issues before deployment.',
    'dfarm.s2': 'Project Management Approach',
    'dfarm.s2c1': 'Team Coordination',
    'dfarm.s2c1p': 'Leading daily standups, sprint planning, and retrospectives to ensure smooth collaboration between development, design, and testing teams.',
    'dfarm.s2c2': 'Timeline Management',
    'dfarm.s2c2p': 'Creating and maintaining project schedules, identifying critical path dependencies, and managing stakeholder expectations throughout the development cycle.',
    'dfarm.s2c3': 'Risk Mitigation',
    'dfarm.s2c3p': 'Proactively identifying potential risks, creating contingency plans, and ensuring project deliverables meet quality standards and deadlines.',
    'dfarm.s2c4': 'Quality Assurance',
    'dfarm.s2c4p': 'Establishing comprehensive testing protocols, conducting manual testing sessions, and maintaining structured bug tracking and resolution processes.',
    'dfarm.s3': 'Quality Assurance Methodology',
    'dfarm.s3s1': 'Test Planning',
    'dfarm.s3s1p': 'Creating comprehensive test plans covering functional, integration, and user acceptance testing for all system modules.',
    'dfarm.s3s2': 'Manual Testing',
    'dfarm.s3s2p': 'Executing systematic manual testing on web and mobile platforms, focusing on user workflows, data integrity, and system performance.',
    'dfarm.s3s3': 'Bug Tracking',
    'dfarm.s3s3p': 'Documenting and prioritizing issues using structured bug reports, coordinating with the development team for timely resolution.',
    'dfarm.s3s4': 'Validation',
    'dfarm.s3s4p': 'Conducting regression testing and final validation to ensure all features function correctly before production deployment.',
    'dfarm.s4': 'Core Visual Elements',
    'dfarm.s4p': 'Consistent visual language for a cohesive user experience. These are the foundational elements that guided the quality assurance process.',
    'dfarm.s4c1': 'Primary Color Palette',
    'dfarm.s4c2': 'Typography Scale',
    'dfarm.s5': 'Designing with Clarity',
    'dfarm.s5p': 'Real-world mockups showing how the final UI looks on actual devices. During QA, I focused on ensuring the interface felt familiar and easy to use from the user\'s hand.',
    'dfarm.s6': 'Managed with Precision, Tested with Care',
    'dfarm.s6p1': 'This project management and quality assurance approach was built with intention and precision, drawing from methodologies I\'ve refined through experience coordinating complex technical projects. Every detail — from sprint planning to bug tracking workflows — is designed to ensure successful delivery with the highest quality standards.',
    'dfarm.s6p2': 'The comprehensive testing processes and project coordination developed for D\'Farm Dream continue to form the foundation of my approach to complex technical projects.',
    'figma.hero': 'Mobile and web application interface design with a focus on user experience. From wireframing to prototyping — every element is designed to help users achieve their goals effortlessly.',
    'figma.metaRole': 'Role',
    'figma.metaTools': 'Tools',
    'figma.s1': 'Design Process',
    'figma.s1p': 'Every design project begins with understanding user needs, then translating them into an intuitive and visually appealing interface.',
    'figma.s1r1': 'Research',
    'figma.s1r1p': 'Analyzing user needs, competitors, and relevant design trends.',
    'figma.s1r2': 'Wireframe',
    'figma.s1r2p': 'Sketching layout and information structure before moving to visual design.',
    'figma.s1r3': 'Visual Design',
    'figma.s1r3p': 'Defining color palette, typography, and cohesive visual elements.',
    'figma.s1r4': 'Prototype',
    'figma.s1r4p': 'Building interactive prototypes for user flow testing before development.',
    'figma.s2': 'Selected Works',
    'figma.s2p': 'A selection of designs I\'ve worked on, from mobile apps to web dashboards.',
    'figma.s2c1': 'SmartHome Control',
    'figma.s2c1p': 'Mobile app design for monitoring and controlling smart home IoT devices. Focused on real-time data and easy access.',
    'figma.s2c2': 'YPOK Admin Dashboard',
    'figma.s2c2p': 'Admin interface for managing karate member data, diploma verification, and directory search. Clean and functional.',
    'figma.s2c3': 'Lebak Ciherang Website',
    'figma.s2c3p': 'Educational tourism website with booking integration. Inviting design with intuitive navigation for visitors.',
    'figma.s2c4': 'D\'Farm Dream ERP',
    'figma.s2c4p': 'Mobile interface for farm ERP systems. Coop monitoring, feed stock, and production data at your fingertips.',
    'figma.s3': 'Tools & Skills',
    'figma.s3p': 'Tools I use to translate ideas into designs ready for development.',
    'figma.s4': 'Designed with Users in Mind',
    'figma.s4p1': 'Every design I create starts with a simple question: "how can I make this easier for users?" From there, I build wireframes, iterate visually, and create prototypes that can be tested immediately.',
    'figma.s4p2': 'I believe great design isn\'t just about looking good — it\'s about how the interface communicates with users without needing much explanation.'
  }
};

let currentLang = localStorage.getItem('lang') || 'id';

function setLang(lang) {
  currentLang = lang;
  localStorage.setItem('lang', lang);
  const t = translations[lang];
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key]) el.textContent = t[key];
  });
  const langLabel = document.getElementById('langLabel');
  if (langLabel) langLabel.textContent = lang === 'id' ? 'EN' : 'ID';
  document.documentElement.lang = lang;
  if (typeof buildCards === 'function') buildCards();
  if (typeof renderCarousel === 'function') renderCarousel();
  document.querySelectorAll('.skill-bar-fill').forEach(fill => {
    fill.style.width = '0';
    setTimeout(() => { fill.style.width = fill.dataset.pct + '%'; }, 100);
  });
}

const langToggle = document.getElementById('langToggle');
if (langToggle) {
  langToggle.addEventListener('click', () => {
    setLang(currentLang === 'id' ? 'en' : 'id');
  });
}

// ===== Work Section: 3D Carousel =====
const projects = [
  {
    title: 'YPOK',
    subtitle: 'Yayasan Pendidikan Olahraga Karate',
    category: 'WEB APP',
    desc_id: 'Website resmi yayasan karate dengan direktori anggota, verifikasi ijazah, admin dashboard. PWA dengan integrasi Google Sheets.',
    desc_en: 'Official karate foundation website with member directory, diploma verification, admin dashboard. PWA with Google Sheets integration.',
    tags: ['HTML', 'CSS', 'JS', 'Firebase'],
    link: 'https://ypokidn-e6fad.web.app',
    bg: 'ypok',
    img: 'image/ypok.jpg'
  },
  {
    title: 'SmartHome',
    subtitle: 'IoT Dashboard Skripsi',
    category: 'IOT + PWA',
    desc_id: 'Dashboard IoT untuk skripsi — monitoring sensor suhu, kelembapan, cahaya, gas dengan kontrol lampu & kipas real-time.',
    desc_en: 'IoT dashboard for thesis — monitoring temperature, humidity, light, gas sensors with real-time light & fan control.',
    tags: ['Arduino', 'Firebase', 'HTML', 'JS'],
    link: 'https://skripsi-smarthome-e6971.web.app/',
    bg: 'smart',
    img: 'image/smarthome.jpg'
  },
  {
    title: 'Lebak Ciherang',
    subtitle: 'Eco-Edu Tourism',
    category: 'WEBSITE',
    desc_id: 'Website wisata edukasi & alam di Bogor. Paket trekking, camping, galeri foto, booking via WhatsApp.',
    desc_en: 'Eco-education & nature tourism website in Bogor. Trekking packages, camping, photo gallery, WhatsApp booking.',
    tags: ['HTML', 'CSS', 'JS', 'Firebase'],
    link: 'http://lebak-ciherang.web.app',
    bg: 'lebak',
    img: 'image/lebak-ciherang.jpg'
  },
  {
    title: 'D\'Farm Dream',
    subtitle: 'Poultry ERP System',
    category: 'ERP',
    desc_id: 'Sistem ERP berbasis IoT untuk peternakan unggas. Project Manager & QA Manual — testing protokol dan integrasi sistem.',
    desc_en: 'IoT-based ERP system for poultry farming. Project Manager & QA Manual — protocol testing and system integration.',
    tags: ['PM', 'QA', 'IoT'],
    link: 'dfarmdream.html',
    bg: 'dfarm',
    img: 'image/dfarm.jpg'
  },
  {
    title: 'UI/UX Design',
    subtitle: 'Mobile App Design',
    category: 'DESIGN',
    desc_id: 'Desain antarmuka mobile app dengan fokus user experience. Wireframing, prototyping, dan design system di Figma.',
    desc_en: 'Mobile app interface design focused on user experience. Wireframing, prototyping, and design system in Figma.',
    tags: ['Figma', 'UI/UX'],
    link: 'figma.html',
    bg: 'figma',
    img: 'image/figma-ui.jpg'
  }
];

let activeIndex = 0;
const track = document.getElementById('carouselTrack');
const dotsContainer = document.getElementById('carouselDots');
const counterEl = document.getElementById('carouselCounter');
const prevBtn = document.getElementById('carouselPrev');
const nextBtn = document.getElementById('carouselNext');

function buildCards() {
  if (!track) return;
  track.innerHTML = '';
  projects.forEach((p, i) => {
    const card = document.createElement('div');
    card.className = 'carousel-card';
    card.dataset.index = i;
    card.innerHTML = `
      <div class="card-thumb">
        <div class="card-thumb-bg card-thumb-bg--${p.bg}"></div>
        <div class="card-scanline"></div>
        <span class="card-category">${p.category}</span>
        <div class="laptop-mockup">
          <div class="laptop-screen">
            <div class="laptop-topbar">
              <span class="laptop-dot"></span>
              <span class="laptop-dot"></span>
              <span class="laptop-dot"></span>
              <div class="laptop-url"></div>
            </div>
            <div class="laptop-display">
              <div class="laptop-placeholder">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                <span>${p.title}</span>
              </div>
              ${p.img ? `<img src="${p.img}" alt="${p.title}" class="laptop-img" style="display:none">` : ''}
            </div>
          </div>
          <div class="laptop-base">
            <div class="laptop-notch"></div>
          </div>
        </div>
      </div>
      <div class="card-info">
        <h3 class="card-title">${p.title}</h3>
        <p>${currentLang === 'id' ? p.desc_id : p.desc_en}</p>
        <div class="card-tags">${p.tags.map(t => `<span class="card-tag">${t}</span>`).join('')}</div>
        <a href="${p.link}" class="card-link" ${p.link.startsWith('http') ? 'target="_blank"' : ''}>${currentLang === 'id' ? translations.id['project.live'] : translations.en['project.live']} &rarr;</a>
      </div>
    `;
    card.addEventListener('click', (e) => {
      if (e.target.closest('.card-link')) return;
      if (i === activeIndex) {
        // Card is already in front/center — go straight to the project.
        if (p.link.startsWith('http')) {
          window.open(p.link, '_blank');
        } else {
          window.location.href = p.link;
        }
      } else {
        goTo(i);
      }
    });
    const imgEl = card.querySelector('.laptop-img');
    const placeholderEl = card.querySelector('.laptop-placeholder');
    if (imgEl) {
      imgEl.addEventListener('load', () => {
        imgEl.style.display = 'block';
        if (placeholderEl) placeholderEl.style.display = 'none';
      });
      imgEl.addEventListener('error', () => {
        // Photo not added to /image yet — keep showing the placeholder instead of a broken image icon.
        imgEl.remove();
      });
    }
    track.appendChild(card);
  });
  updateCarouselHeight();
}

// ===== Keep carousel height matched to tallest card content (prevents Live Demo button being clipped on mobile) =====
function updateCarouselHeight() {
  if (!track) return;
  if (window.innerWidth <= 768) {
    track.style.minHeight = '';
    return;
  }
  const cards = track.querySelectorAll('.carousel-card');
  let maxH = 0;
  cards.forEach(card => {
    if (card.offsetHeight > maxH) maxH = card.offsetHeight;
  });
  if (maxH > 0) {
    track.style.minHeight = (maxH + 40) + 'px';
  }
}

function buildDots() {
  if (!dotsContainer) return;
  dotsContainer.innerHTML = '';
  projects.forEach((_, i) => {
    const dot = document.createElement('div');
    dot.className = 'carousel-dot' + (i === activeIndex ? ' carousel-dot--active' : '');
    dot.addEventListener('click', () => goTo(i));
    dotsContainer.appendChild(dot);
  });
}

function renderCarousel() {
  const cards = track.querySelectorAll('.carousel-card');
  const isMobile = window.innerWidth <= 768;
  cards.forEach((card, i) => {
    card.classList.remove('carousel-card--active', 'carousel-card--left', 'carousel-card--right', 'carousel-card--hidden');
    if (isMobile) {
      card.classList.add('carousel-card--active');
    } else {
      if (i === activeIndex) {
        card.classList.add('carousel-card--active');
      } else if (i === activeIndex - 1 || (activeIndex === 0 && i === projects.length - 1)) {
        card.classList.add('carousel-card--left');
      } else if (i === activeIndex + 1 || (activeIndex === projects.length - 1 && i === 0)) {
        card.classList.add('carousel-card--right');
      } else {
        card.classList.add('carousel-card--hidden');
      }
    }
  });

  if (dotsContainer) {
    dotsContainer.querySelectorAll('.carousel-dot').forEach((dot, i) => {
      dot.classList.toggle('carousel-dot--active', i === activeIndex);
    });
  }
  if (counterEl) {
    counterEl.textContent = `${String(activeIndex + 1).padStart(2, '0')} / ${String(projects.length).padStart(2, '0')}`;
  }
}

function goTo(idx) {
  activeIndex = idx;
  renderCarousel();
  updateCarouselHeight();
}

if (prevBtn) prevBtn.addEventListener('click', () => goTo(activeIndex > 0 ? activeIndex - 1 : projects.length - 1));
if (nextBtn) nextBtn.addEventListener('click', () => goTo(activeIndex < projects.length - 1 ? activeIndex + 1 : 0));

let carouselResizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(carouselResizeTimer);
  carouselResizeTimer = setTimeout(updateCarouselHeight, 150);
});

buildCards();
buildDots();
renderCarousel();

// ===== Work Section: Canvas Wireframe Background =====
(function() {
  const canvas = document.getElementById('workBgCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let w, h, particles = [], mouse = { x: -1000, y: -1000 };

  function resize() {
    const section = canvas.parentElement;
    w = canvas.width = section.offsetWidth;
    h = canvas.height = section.offsetHeight;
  }

  class Particle {
    constructor() {
      this.x = Math.random() * w;
      this.y = Math.random() * h;
      this.vx = (Math.random() - 0.5) * 0.4;
      this.vy = (Math.random() - 0.5) * 0.4;
      this.r = Math.random() * 1.5 + 0.5;
      this.color = Math.random() > 0.5 ? 'rgba(34,211,238,' : 'rgba(168,85,247,';
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      if (this.x < 0 || this.x > w) this.vx *= -1;
      if (this.y < 0 || this.y > h) this.vy *= -1;
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = this.color + '0.4)';
      ctx.fill();
    }
  }

  function init() {
    resize();
    particles = [];
    const count = Math.min(Math.floor((w * h) / 12000), 70);
    for (let i = 0; i < count; i++) {
      particles.push(new Particle());
    }
  }

  function drawGrid() {
    const spacing = 60;
    ctx.strokeStyle = 'rgba(34,211,238,0.04)';
    ctx.lineWidth = 0.5;
    for (let x = 0; x < w; x += spacing) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, h);
      ctx.stroke();
    }
    for (let y = 0; y < h; y += spacing) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }
  }

  function drawConnections() {
    const maxDist = 120;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < maxDist) {
          const alpha = (1 - dist / maxDist) * 0.12;
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(34,211,238,${alpha})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
  }

  function animate() {
    ctx.clearRect(0, 0, w, h);
    drawGrid();
    particles.forEach(p => { p.update(); p.draw(); });
    drawConnections();
    requestAnimationFrame(animate);
  }

  window.addEventListener('resize', () => { resize(); });
  canvas.parentElement.addEventListener('mousemove', (e) => {
    const rect = canvas.parentElement.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  });

  init();
  animate();
})();

// ===== Contact Item Stagger =====
const contactObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const items = entry.target.querySelectorAll('.contact-item');
      items.forEach((item, i) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        setTimeout(() => {
          item.style.transition = 'opacity 0.5s, transform 0.5s';
          item.style.opacity = '1';
          item.style.transform = 'translateX(0)';
        }, i * 100);
      });
      contactObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.2 });

const contactLinks = document.querySelector('.contact-links');
if (contactLinks) contactObserver.observe(contactLinks);

// ===== Random Color Cursor on Section Hover =====
const sectionColors = {
  'home': 'var(--cyan)',
  'skills': 'var(--purple)',
  'work': 'var(--cyan)',
  'facts': 'var(--purple)',
  'contact': 'var(--purple)'
};

const colorSections = document.querySelectorAll('[id]');
colorSections.forEach(section => {
  const color = sectionColors[section.id];
  if (color && cursor && follower) {
    section.addEventListener('mouseenter', () => {
      cursor.style.background = color;
      follower.style.borderColor = color;
    });
  }
});

// ===== Tilt on Approach/QA Cards =====
const tiltCards = document.querySelectorAll('.approach-card, .qa-step, .process-step, .design-card');
tiltCards.forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    card.style.transform = `perspective(600px) rotateY(${x * 6}deg) rotateX(${-y * 6}deg) translateY(-2px)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = 'perspective(600px) rotateY(0) rotateX(0) translateY(0)';
  });
});

// ===== Tool Chip Hover Color =====
const toolChips = document.querySelectorAll('.tool-chip');
toolChips.forEach(chip => {
  chip.addEventListener('mouseenter', () => {
    const colors = ['var(--cyan)', 'var(--purple)', '#a855f7', '#22d3ee', '#06b6d4'];
    const color = colors[Math.floor(Math.random() * colors.length)];
    chip.style.borderColor = color;
    chip.style.color = color;
  });
  chip.addEventListener('mouseleave', () => {
    chip.style.borderColor = '';
    chip.style.color = '';
  });
});

// ===== Visitor Counter =====
const visitorKey = 'rtw_visitor_' + Math.floor(Date.now() / 30000);
let visitorCount = 1;

function updateVisitorDisplay() {
  const el = document.getElementById('visitorCount');
  if (el) el.textContent = visitorCount;
}

function trackVisitor() {
  const now = Date.now();
  const visitors = JSON.parse(localStorage.getItem('rtw_visitors') || '[]');
  const active = visitors.filter(t => now - t < 30000);
  active.push(now);
  localStorage.setItem('rtw_visitors', JSON.stringify(active));
  visitorCount = active.length;
  updateVisitorDisplay();
}

trackVisitor();
setInterval(trackVisitor, 5000);