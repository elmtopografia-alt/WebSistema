import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Check,
  Sparkles,
  Zap,
  Crown,
  MessageCircle,
  CreditCard,
} from 'lucide-react';

const plans = [
  {
    name: 'Mensal',
    description: 'Sem fidelidade',
    price: '30,00',
    period: '/mês',
    discount: null,
    badge: null,
    features: [
      'Acesso Completo',
      'Multi-Plataforma',
      'Suporte Básico',
      'Backup Semanal',
      '1 Usuário',
    ],
    cta: 'Pagar com PIX',
    ctaIcon: CreditCard,
    href: 'https://mpago.la/2JrbxWt',
    popular: false,
    color: 'blue',
  },
  {
    name: 'Trimestral',
    description: 'Cobrado R$ 85,50 a cada 3 meses',
    price: '28,50',
    period: '/mês',
    discount: '5% OFF',
    badge: null,
    features: [
      'Tudo do Mensal',
      'Desconto de 5%',
      'Renovação Manual',
      'Backup Semanal',
      '2 Usuários',
    ],
    cta: 'Assinar Trimestral',
    ctaIcon: CreditCard,
    href: 'https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af',
    popular: false,
    color: 'purple',
  },
  {
    name: 'Semestral',
    description: 'Cobrado R$ 162,00 a cada 6 meses',
    price: '27,00',
    period: '/mês',
    discount: '10% OFF',
    badge: null,
    features: [
      'Tudo do Mensal',
      'Desconto de 10%',
      'Prioridade no Suporte',
      'Backup Diário',
      '3 Usuários',
    ],
    cta: 'Assinar Semestral',
    ctaIcon: Zap,
    href: 'https://mpago.la/2MjigKn',
    popular: false,
    color: 'green',
  },
  {
    name: 'Anual',
    description: 'Cobrado R$ 288,00 anualmente',
    price: '24,00',
    period: '/mês',
    discount: '20% OFF',
    badge: 'MELHOR ESCOLHA',
    features: [
      '20% de Desconto',
      'Acesso Vitalício aos Dados',
      'Suporte VIP 24/7',
      'Backup Diário',
      'Usuários Ilimitados',
      'API de Integração',
    ],
    cta: 'Assinar Agora',
    ctaIcon: Crown,
    href: 'https://mpago.la/1CuvPFA',
    popular: true,
    color: 'orange',
  },
];

export default function Pricing() {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-100px' });
  const [hoveredPlan, setHoveredPlan] = useState<string | null>(null);

  return (
    <section id="planos" className="relative py-24 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-b from-transparent via-blue-500/5 to-transparent" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Section header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5 }}
          className="text-center mb-16"
        >
          <span className="inline-block px-4 py-2 rounded-full bg-green-500/10 text-green-400 text-sm font-medium mb-4 border border-green-500/20">
            Planos
          </span>
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
            Escolha o plano{' '}
            <span className="text-gradient">Ideal</span>
          </h2>
          <p className="text-slate-400 text-lg max-w-2xl mx-auto">
            Desbloqueie o potencial máximo do seu negócio com nossos planos flexíveis.
            Comece hoje mesmo.
          </p>
        </motion.div>

        {/* Pricing cards */}
        <motion.div
          ref={ref}
          initial={{ opacity: 0 }}
          animate={isInView ? { opacity: 1 } : {}}
          transition={{ duration: 0.5 }}
          className="grid md:grid-cols-2 lg:grid-cols-4 gap-6"
        >
          {plans.map((plan, index) => (
            <motion.div
              key={plan.name}
              initial={{ opacity: 0, y: 30 }}
              animate={isInView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.5, delay: index * 0.1 }}
              onMouseEnter={() => setHoveredPlan(plan.name)}
              onMouseLeave={() => setHoveredPlan(null)}
              className={`relative rounded-2xl p-6 transition-all duration-300 ${
                plan.popular
                  ? 'bg-gradient-to-b from-orange-500/20 to-orange-600/10 border-2 border-orange-500/50 scale-105 lg:scale-110'
                  : 'bg-[#111827] border border-white/10 hover:border-white/20'
              } ${hoveredPlan === plan.name ? 'transform -translate-y-2' : ''}`}
            >
              {/* Popular badge */}
              {plan.badge && (
                <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                  <Badge className="bg-gradient-to-r from-orange-500 to-orange-600 text-white border-0 px-4 py-1">
                    <Sparkles className="w-3 h-3 mr-1" />
                    {plan.badge}
                  </Badge>
                </div>
              )}

              {/* Discount badge */}
              {plan.discount && (
                <div className="absolute top-4 right-4">
                  <span
                    className={`text-xs font-bold px-2 py-1 rounded-full ${
                      plan.color === 'green'
                        ? 'bg-green-500/20 text-green-400'
                        : plan.color === 'purple'
                        ? 'bg-purple-500/20 text-purple-400'
                        : 'bg-blue-500/20 text-blue-400'
                    }`}
                  >
                    {plan.discount}
                  </span>
                </div>
              )}

              {/* Plan header */}
              <div className="mb-6">
                <h3 className="text-xl font-bold text-white mb-1">{plan.name}</h3>
                <p className="text-sm text-slate-500">{plan.description}</p>
              </div>

              {/* Price */}
              <div className="mb-6">
                <div className="flex items-baseline gap-1">
                  <span className="text-lg text-slate-400">R$</span>
                  <span className="text-4xl font-bold text-white">{plan.price}</span>
                  <span className="text-slate-500">{plan.period}</span>
                </div>
              </div>

              {/* Features */}
              <ul className="space-y-3 mb-8">
                {plan.features.map((feature) => (
                  <li key={feature} className="flex items-start gap-3">
                    <div
                      className={`w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 ${
                        plan.popular
                          ? 'bg-orange-500/20'
                          : plan.color === 'green'
                          ? 'bg-green-500/20'
                          : plan.color === 'purple'
                          ? 'bg-purple-500/20'
                          : 'bg-blue-500/20'
                      }`}
                    >
                      <Check
                        className={`w-3 h-3 ${
                          plan.popular
                            ? 'text-orange-400'
                            : plan.color === 'green'
                            ? 'text-green-400'
                            : plan.color === 'purple'
                            ? 'text-purple-400'
                            : 'text-blue-400'
                        }`}
                      />
                    </div>
                    <span className="text-sm text-slate-400">{feature}</span>
                  </li>
                ))}
              </ul>

              {/* CTA Button */}
              <Button
                className={`w-full btn-shine ${
                  plan.popular
                    ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white shadow-lg shadow-orange-500/25'
                    : plan.color === 'green'
                    ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white'
                    : plan.color === 'purple'
                    ? 'bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white'
                    : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white'
                }`}
                asChild
              >
                <a href={plan.href}>
                  <plan.ctaIcon className="w-4 h-4 mr-2" />
                  {plan.cta}
                </a>
              </Button>
            </motion.div>
          ))}
        </motion.div>

        {/* Security note */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5, delay: 0.4 }}
          className="mt-12 text-center"
        >
          <p className="text-slate-500 text-sm mb-4">
            Pagamento 100% seguro via Mercado Pago. Ativação imediata.
          </p>
          <Button
            variant="outline"
            className="border-green-500/30 text-green-400 hover:text-green-300 hover:bg-green-500/10"
            asChild
          >
            <a
              href="https://api.whatsapp.com/send?phone=5531971875928&text=Falar%20com%20um%20Consultor!"
              target="_blank"
              rel="noopener noreferrer"
            >
              <MessageCircle className="w-4 h-4 mr-2" />
              Falar com Consultor
            </a>
          </Button>
        </motion.div>
      </div>
    </section>
  );
}
