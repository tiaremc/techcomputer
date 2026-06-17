import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const TEMPLATES = path.join(__dirname, 'templates');

const REPLACEMENTS = [
  ['ElectroMart', 'Techcomputer'],
  ['electromart.com', 'ventas@techcomputer.cl'],
  ['Smart Living Starts Here', 'Servicio Técnico Notebook en Santiago'],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ornare erat eget lorem cursus suscipit. Aliquam non libero velit.',
    'En TECHCOMPUTER somos especialistas en servicio técnico de notebooks en Santiago. Reparamos, optimizamos y recuperamos tu equipo con atención rápida, diagnóstico profesional y garantía en cada servicio.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam vitae nisl et massa pretium congue.',
    'Servicio técnico especializado en reparación de notebooks, cambio de pantalla, SSD, teclado, bisagras y mantención profesional en Las Condes y Santiago.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ornare erat eget lorem cursus suscipit.',
    'Servicio rápido y profesional con repuestos de calidad y técnicos especializados. Garantía escrita en cada trabajo.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'Diagnóstico profesional, atención rápida y garantía escrita en Santiago.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum est mi, iaculis id risus eu, maximus maximus libero. Curabitur dapibus euismod dui, eu vestibulum sem ornare sed. Aenean mollis ultrices vulputate.',
    'Contamos con más de 8 años de experiencia reparando notebooks HP, Lenovo, Dell, Asus y Acer. Ofrecemos diagnóstico profesional, repuestos de calidad y garantía escrita en cada servicio técnico.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sed ex sit amet nibh eleifend pharetra vitae ac lacus. Cras accumsan ex sollicitudin urna bibendum, id elementum nisi laoreet. Pellentesque vitae dui in orci congue tempor.\u00a0',
    'En Techcomputer ayudamos a personas y empresas a reparar, optimizar y recuperar sus notebooks con atención rápida y diagnóstico profesional.',
  ],
  [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sed ex sit amet nibh eleifend pharetra vitae ac lacus. Cras accumsan ex sollicitudin urna bibendum, id elementum nisi laoreet. Pellentesque vitae dui in orci congue tempor. Morbi felis turpis, tristique a libero in, faucibus tempus dui. Phasellus nec lacus orci. Fusce in sem fermentum, pellentesque velit in, facilisis purus.',
    'Nuestro compromiso es entregar soluciones confiables para todo tipo de fallas: cambio de pantalla, reparación de bisagras, cambio de teclado, instalación de SSD, mantenimiento interno y problemas de carga, temperatura o rendimiento lento. Tu notebook en manos expertas.',
  ],
  ['Explore Product', 'Cotiza Aquí'],
  ['Explore Category', 'Ver Servicio'],
  ['Explore Categories', 'Ver Servicios'],
  ['About Us', 'Nosotros'],
  ['View More', 'Ver Más'],
  ['Return Home', 'Volver al Inicio'],
  ['Limited-Time Summer Steals!', '¡Cambiamos tu pantalla en solo 30 minutos!'],
  ['Best Selling Product', 'Nuestros Servicios'],
  ['Featured Product', 'Servicios Destacados'],
  ['Subscribe To Our Email Newsletter', 'Contáctanos por WhatsApp'],
  ['What Our Customers Are Saying', 'La confianza de miles de clientes satisfechos'],
  ['Our Articles And News', 'Consejos y Novedades'],
  ['Built for Your Everyday Needs', 'Con más de 8 años de experiencia'],
  ['What We Offer', 'Servicio Técnico Notebook especializado'],
  ["Got Questions? We've Got Answers", '¿Tienes dudas? Te respondemos'],
  ['Get In Touch', 'Contáctanos'],
  ['Have an Idea? Let\u2019s Make It Happen', '¿Necesitas reparar tu notebook?'],
  ['Page Not Found', 'Página no encontrada'],
  ['Happy Clients', 'Clientes Conformes'],
  ['Market Rating', 'Nota en Google'],
  ['Years in Marketplace', 'Años de experiencia'],
  ['Team Member', 'Reseñas positivas'],
  ['Satisfaction Rate', 'Clientes satisfechos'],
  ['Accessory', 'Cambio de Pantalla'],
  ['Gear', 'Reparación de Bisagras'],
  ['Audio', 'Reparación de Consolas'],
  ['Technology', 'Kit Actualización'],
  ['Design', 'Cambio de Pantalla'],
  ['Sustainability', 'Reparación de Bisagras'],
  ['Quality', 'Reparación de Consolas'],
  ['Innovation', 'Kit Actualización'],
  ['Team Work', 'Mantención Notebook'],
  ['Navigation', 'Navegación'],
  ['Quick Links', 'Enlaces'],
  ['Information', 'Información'],
  ['Home', 'Inicio'],
  ['Services', 'Servicios'],
  ['Products', 'Servicios'],
  ['Product', 'Repuestos'],
  ['Pages', 'Páginas'],
  ['Contact Us', 'Contáctanos'],
  ['FAQ', 'Preguntas Frecuentes'],
  ['News', 'Noticias'],
  ['Testimonials', 'Testimonios'],
  ['201 Air Street, 3rd Floor', 'Los Militares 5620, Oficina 1801, Las Condes'],
  ['(105) 115-2920', '+56 9 3219 4619'],
  ['support@electromart.com', 'ventas@techcomputer.cl'],
  [
    '"Fast delivery, fresh items, and amazing service. I shop here every week!"',
    '"Excelente atención y solución del problema. Logré recuperar un notebook que estuve a punto de tirar a reciclaje."',
  ],
  [
    '"Super easy to shop and the prices are great. I love how smooth the whole experience is!"',
    '"Solución rápida e información clara desde un comienzo, muy amables en todo, los recomiendo 100%."',
  ],
  [
    '"Fresh products, fast delivery, and super easy to shop. Always a great experience!"',
    '"Profesionales, prolijos, buena atención al cliente y muy buen precio. El cambio de pantalla demoró 30 minutos."',
  ],
  ['James Miller', 'Paulo Villagrán Salinas'],
  ['Daniel Brooks ', 'Valentina Gonzalez'],
  ['Ethan Parker', 'Matti Marchese'],
  ['Are the products original and under warranty?', '¿Ofrecen garantía en las reparaciones?'],
  ['How long does shipping take?', '¿Cuánto demora una reparación?'],
  ['Do you offer installment plans?', '¿Qué marcas de notebook reparan?'],
];

const NUMERIC = [
  ['"ending_number": 355', '"ending_number": 1150'],
  ['"ending_number": 500', '"ending_number": 800'],
  ['"ending_number": 15', '"ending_number": 8'],
  ['"ending_number": 35', '"ending_number": 1150'],
  ['"ending_number": 95', '"ending_number": 98'],
  ['"title_text": "4.5"', '"title_text": "9.5"'],
];

const SERVICE_DESCRIPTIONS = {
  'Cambio de Pantalla':
    'Servicio rápido y profesional de cambio de pantalla para notebooks. Reparación garantizada con repuestos originales, ideal para todas las marcas.',
  'Reparación de Bisagras':
    'Reparación experta de bisagras para notebook. Soluciones rápidas y seguras para prolongar la vida útil de tu equipo.',
  'Reparación de Consolas':
    'Reparamos consolas de videojuegos de todas las marcas: problemas de encendido y actualización de software. ¡Servicio garantizado!',
  'Kit Actualización':
    'Mejora el rendimiento de tu notebook con nuestro servicio de upgrade: más velocidad, almacenamiento y eficiencia para un mejor desempeño.',
  'Mantención Notebook':
    'Servicio de limpieza profesional para notebooks: elimina polvo, mejora el rendimiento, evita sobrecalentamiento y prolonga la vida útil del equipo.',
};

function apply(content) {
  for (const [old, rep] of REPLACEMENTS) content = content.split(old).join(rep);
  for (const [old, rep] of NUMERIC) content = content.split(old).join(rep);

  for (const [title, desc] of Object.entries(SERVICE_DESCRIPTIONS)) {
    content = content.replace(
      `"title_text": "${title}",\n                                        "description_text": "Diagnóstico profesional, atención rápida y garantía escrita en Santiago."`,
      `"title_text": "${title}",\n                                        "description_text": "${desc}"`,
    );
    content = content.replace(
      `"title_text": "${title}",\n                                        "description_text": "Servicio rápido y profesional con repuestos de calidad y técnicos especializados. Garantía escrita en cada trabajo."`,
      `"title_text": "${title}",\n                                        "description_text": "${desc}"`,
    );
  }

  content = content.replace(
    '"ending_number": 800,\n                                        "suffix": "+",\n                                        "title": "Servicios"',
    '"ending_number": 6,\n                                        "suffix": "",\n                                        "title": "Servicios"',
  );
  content = content.replace(
    '"ending_number": 800,\n                                "suffix": "+",\n                                "title": "Repuestos"',
    '"ending_number": 800,\n                                "suffix": "+",\n                                "title": "Reseñas Google"',
  );

  return content;
}

const updated = [];
for (const file of fs.readdirSync(TEMPLATES).filter((f) => f.endsWith('.json'))) {
  const filePath = path.join(TEMPLATES, file);
  const original = fs.readFileSync(filePath, 'utf8');
  const modified = apply(original);
  if (modified !== original) {
    fs.writeFileSync(filePath, modified, 'utf8');
    updated.push(file);
  }
}

const manifestPath = path.join(__dirname, 'manifest.json');
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
manifest.title = 'Techcomputer - Servicio Técnico Notebook Elementor Template Kit';
fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 4), 'utf8');

console.log(`Updated ${updated.length} template files:`);
updated.forEach((f) => console.log(`  - ${f}`));
console.log('Updated manifest.json');
