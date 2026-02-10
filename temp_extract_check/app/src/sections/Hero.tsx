import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Database,
  Shield,
  Smartphone,
  ArrowRight,
  Crown,
  Rocket,
  ShoppingCart,
} from 'lucide-react';

const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.2,
    },
  },
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: 'easeOut' as const },
  },
};

const badges = [
  { icon: Database, text: 'MySQL' },
  { icon: Shield, text: 'Seguro' },
  { icon: Smartphone, text: 'Responsivo' },
];

export default function Hero() {
  return (
    <section
      id="inicio"
      className="relative min-h-screen flex items-center pt-20 pb-16 overflow-hidden"
    >
      {/* Background gradient */}
      <div className="absolute inset-0 radial-gradient" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          {/* Left content */}
          <motion.div
            variants={containerVariants}
            initial="hidden"
            animate="visible"
            className="text-center lg:text-left"
          >
            <motion.div variants={itemVariants}>
              <Badge
                variant="secondary"
                className="mb-6 px-4 py-2 bg-orange-500/10 text-orange-400 border border-orange-500/20 hover:bg-orange-500/20"
              >
                <Sparkles className="w-3.5 h-3.5 mr-1.5" />
                Sistema SaaS de Gestão
              </Badge>
            </motion.div>

            <motion.h1
              variants={itemVariants}
              className="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6"
            >
              <span className="text-white">SGT-</span>
              <span className="text-gradient">Propostas</span>
            </motion.h1>

            <motion.p
              variants={itemVariants}
              className="text-xl sm:text-2xl text-slate-300 font-medium mb-4"
            >
              Gestão de Prosperidade
            </motion.p>

            <motion.p
              variants={itemVariants}
              className="text-slate-400 text-base sm:text-lg mb-8 max-w-xl mx-auto lg:mx-0"
            >
              Sistema integrado ao MySQL, seguro e acessível. Transforme leads em
              contratos fechados com nossa solução SaaS completa.
            </motion.p>

            {/* CTA Buttons */}
            <motion.div
              variants={itemVariants}
              className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-10"
            >
              <div className="space-y-3">
                <p className="text-xs text-slate-500 uppercase tracking-wider font-medium">
                  Já sou cliente
                </p>
                <div className="flex flex-col sm:flex-row gap-3">
                  <Button
                    size="lg"
                    className="btn-shine bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/25"
                    asChild
                  >
                    <a href="https://sgt-propostas.app/dashboard">
                      <Crown className="w-4 h-4 mr-2" />
                      Login Cliente PRO
                    </a>
                  </Button>
                  <Button
                    size="lg"
                    variant="outline"
                    className="border-white/20 text-slate-300 hover:text-white hover:bg-white/5"
                    asChild
                  >
                    <a href="https://sgt-propostas.app/dashboard">
                      <ArrowRight className="w-4 h-4 mr-2" />
                      Login Demonstração
                    </a>
                  </Button>
                </div>
              </div>

              <div className="space-y-3">
                <p className="text-xs text-slate-500 uppercase tracking-wider font-medium">
                  Quero conhecer
                </p>
                <div className="flex flex-col sm:flex-row gap-3">
                  <Button
                    size="lg"
                    className="btn-shine bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white shadow-lg shadow-orange-500/25"
                    asChild
                  >
                    <a href="#planos">
                      <Rocket className="w-4 h-4 mr-2" />
                      Criar Conta Demo
                    </a>
                  </Button>
                  <Button
                    size="lg"
                    variant="outline"
                    className="border-green-500/30 text-green-400 hover:text-green-300 hover:bg-green-500/10"
                    asChild
                  >
                    <a href="#planos">
                      <ShoppingCart className="w-4 h-4 mr-2" />
                      Adquirir Plano PRO
                    </a>
                  </Button>
                </div>
              </div>
            </motion.div>

            {/* Badges */}
            <motion.div
              variants={itemVariants}
              className="flex flex-wrap gap-3 justify-center lg:justify-start"
            >
              {badges.map((badge) => (
                <div
                  key={badge.text}
                  className="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10"
                >
                  <badge.icon className="w-4 h-4 text-orange-400" />
                  <span className="text-sm text-slate-300">{badge.text}</span>
                </div>
              ))}
            </motion.div>
          </motion.div>

          {/* Right content - Dashboard Preview */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7, delay: 0.3 }}
            className="relative"
          >
            <div className="relative animate-float">
              {/* Glow effect */}
              <div className="absolute -inset-4 bg-gradient-to-r from-orange-500/20 to-blue-500/20 rounded-3xl blur-2xl" />

              {/* Dashboard mockup */}
              <div className="relative bg-[#111827] rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                {/* Browser header */}
                <div className="flex items-center gap-2 px-4 py-3 bg-[#0f172a] border-b border-white/5">
                  <div className="flex gap-1.5">
                    <div className="w-3 h-3 rounded-full bg-red-500" />
                    <div className="w-3 h-3 rounded-full bg-yellow-500" />
                    <div className="w-3 h-3 rounded-full bg-green-500" />
                  </div>
                  <div className="flex-1 text-center">
                    <span className="text-xs text-slate-500">
                      sgt-propostas.app/dashboard
                    </span>
                  </div>
                </div>

                {/* Dashboard content */}
                <div className="p-6">
                  {/* Stats row */}
                  <div className="grid grid-cols-3 gap-4 mb-6">
                    {[
                      { label: 'Custo Total', value: 'R$ 300.000,00', color: 'blue' },
                      { label: 'Margem de Lucro', value: '20%', color: 'green' },
                      { label: 'Valor da Proposta', value: 'R$ 375.000,00', color: 'orange' },
                    ].map((stat) => (
                      <div
                        key={stat.label}
                        className="p-4 rounded-xl bg-white/5 border border-white/10"
                      >
                        <p className="text-xs text-slate-500 mb-1">{stat.label}</p>
                        <p
                          className={`text-sm font-bold ${
                            stat.color === 'blue'
                              ? 'text-blue-400'
                              : stat.color === 'green'
                              ? 'text-green-400'
                              : 'text-orange-400'
                          }`}
                        >
                          {stat.value}
                        </p>
                      </div>
                    ))}
                  </div>

                  {/* Chart area */}
                  <div className="grid grid-cols-2 gap-4">
                    {/* Pie chart */}
                    <div className="p-4 rounded-xl bg-white/5 border border-white/10">
                      <div className="relative w-full aspect-square max-w-[180px] mx-auto">
                        <svg viewBox="0 0 100 100" className="w-full h-full -rotate-90">
                          <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke="#1e293b"
                            strokeWidth="20"
                          />
                          <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke="#3b82f6"
                            strokeWidth="20"
                            strokeDasharray="75.4 175.9"
                          />
                          <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke="#f97316"
                            strokeWidth="20"
                            strokeDasharray="50.3 175.9"
                            strokeDashoffset="-75.4"
                          />
                          <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke="#22c55e"
                            strokeWidth="20"
                            strokeDasharray="37.7 175.9"
                            strokeDashoffset="-125.7"
                          />
                        </svg>
                        <div className="absolute inset-0 flex items-center justify-center">
                          <div className="text-center">
                            <p className="text-xs text-slate-500">Total</p>
                            <p className="text-lg font-bold text-white">R$ 375k</p>
                          </div>
                        </div>
                      </div>
                      <div className="mt-4 space-y-2">
                        {[
                          { label: 'Mão de Obra', value: '35%', color: 'bg-blue-500' },
                          { label: 'Materiais', value: '28%', color: 'bg-orange-500' },
                          { label: 'Equipamentos', value: '22%', color: 'bg-green-500' },
                          { label: 'Outros', value: '15%', color: 'bg-slate-500' },
                        ].map((item) => (
                          <div key={item.label} className="flex items-center justify-between text-xs">
                            <div className="flex items-center gap-2">
                              <div className={`w-2 h-2 rounded-full ${item.color}`} />
                              <span className="text-slate-400">{item.label}</span>
                            </div>
                            <span className="text-slate-300">{item.value}</span>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Table */}
                    <div className="p-4 rounded-xl bg-white/5 border border-white/10">
                      <p className="text-sm font-medium text-white mb-4">Custos vs. Orçamento</p>
                      <div className="space-y-3">
                        {[
                          { cat: 'Mão de Obra', val: 'R$ 105.000,00', pct: '35%' },
                          { cat: 'Materiais', val: 'R$ 84.000,00', pct: '28%' },
                          { cat: 'Equipamentos', val: 'R$ 66.000,00', pct: '22%' },
                          { cat: 'Outros', val: 'R$ 45.000,00', pct: '15%' },
                        ].map((row) => (
                          <div
                            key={row.cat}
                            className="flex items-center justify-between py-2 border-b border-white/5 last:border-0"
                          >
                            <span className="text-xs text-slate-400">{row.cat}</span>
                            <div className="flex items-center gap-4">
                              <span className="text-xs text-slate-300">{row.val}</span>
                              <span className="text-xs text-orange-400 w-8 text-right">
                                {row.pct}
                              </span>
                            </div>
                          </div>
                        ))}
                      </div>
                      <div className="mt-4 pt-3 border-t border-white/10 flex justify-between">
                        <span className="text-sm font-medium text-white">Total</span>
                        <span className="text-sm font-bold text-orange-400">R$ 375.000,00</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}

// Missing import
function Sparkles({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
      <path d="M5 3v4" />
      <path d="M19 17v4" />
      <path d="M3 5h4" />
      <path d="M17 19h4" />
    </svg>
  );
}
