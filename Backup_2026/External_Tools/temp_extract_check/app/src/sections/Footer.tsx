import { motion } from 'framer-motion';
import { Zap, Mail, Phone, MapPin, ExternalLink } from 'lucide-react';

const footerLinks = [
  {
    title: 'Produto',
    links: [
      { name: 'Recursos', href: '#recursos' },
      { name: 'Dashboard', href: '#dashboard' },
      { name: 'Planos', href: '#planos' },
      { name: 'Demo', href: 'https://sgt-propostas.app/dashboard' },
    ],
  },
  {
    title: 'Empresa',
    links: [
      { name: 'Sobre', href: 'https://elmtopografia.com.br' },
      { name: 'Portfolio', href: 'http://www.elmtopografia.com.br/Portfolio.html' },
      { name: 'Contato', href: 'https://elmtopografia.com.br/topografia/index.html#contato' },
    ],
  },
  {
    title: 'Suporte',
    links: [
      { name: 'Central de Ajuda', href: '#' },
      { name: 'Documentação', href: '#' },
      { name: 'WhatsApp', href: 'https://api.whatsapp.com/send?phone=5531971875928' },
    ],
  },
];

export default function Footer() {
  return (
    <footer className="relative bg-[#0f172a] border-t border-white/10">
      {/* Gradient overlay */}
      <div className="absolute inset-0 bg-gradient-to-t from-orange-500/5 to-transparent pointer-events-none" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Main footer */}
        <div className="py-12 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
          {/* Brand */}
          <div className="col-span-2 md:col-span-4 lg:col-span-2">
            <motion.a
              href="#inicio"
              className="flex items-center gap-2 mb-4"
              whileHover={{ scale: 1.02 }}
            >
              <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg">
                <Zap className="w-5 h-5 text-white" />
              </div>
              <div className="flex flex-col">
                <span className="text-lg font-bold text-white leading-tight">SGT</span>
                <span className="text-xs text-orange-400 leading-tight">Propostas</span>
              </div>
            </motion.a>
            <p className="text-slate-400 text-sm mb-6 max-w-sm">
              Sistema integrado ao MySQL, seguro e acessível. Transforme leads em
              contratos fechados com nossa solução SaaS.
            </p>
            <div className="space-y-3">
              <a
                href="mailto:contato@elmtopografia.com.br"
                className="flex items-center gap-2 text-sm text-slate-400 hover:text-orange-400 transition-colors"
              >
                <Mail className="w-4 h-4" />
                contato@elmtopografia.com.br
              </a>
              <a
                href="tel:+5531971875928"
                className="flex items-center gap-2 text-sm text-slate-400 hover:text-orange-400 transition-colors"
              >
                <Phone className="w-4 h-4" />
                (31) 97187-5928
              </a>
              <div className="flex items-start gap-2 text-sm text-slate-400">
                <MapPin className="w-4 h-4 mt-0.5 flex-shrink-0" />
                <span>
                  Av. Francisco Sá 787 Sl 304 - Prado
                  <br />
                  Belo Horizonte - MG
                </span>
              </div>
            </div>
          </div>

          {/* Links */}
          {footerLinks.map((section) => (
            <div key={section.title}>
              <h4 className="text-sm font-semibold text-white mb-4">{section.title}</h4>
              <ul className="space-y-3">
                {section.links.map((link) => (
                  <li key={link.name}>
                    <a
                      href={link.href}
                      className="text-sm text-slate-400 hover:text-orange-400 transition-colors inline-flex items-center gap-1"
                      target={link.href.startsWith('http') ? '_blank' : undefined}
                      rel={link.href.startsWith('http') ? 'noopener noreferrer' : undefined}
                    >
                      {link.name}
                      {link.href.startsWith('http') && (
                        <ExternalLink className="w-3 h-3" />
                      )}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Bottom bar */}
        <div className="py-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-sm text-slate-500">
            © {new Date().getFullYear()} SGT-Propostas. Todos os direitos reservados.
          </p>
          <div className="flex items-center gap-4">
            <span className="text-xs text-slate-500">Tecnologia</span>
            <div className="flex items-center gap-2">
              <span className="px-2 py-1 rounded bg-blue-500/10 text-blue-400 text-xs">
                PHP
              </span>
              <span className="text-slate-600">+</span>
              <span className="px-2 py-1 rounded bg-orange-500/10 text-orange-400 text-xs">
                MySQL
              </span>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
