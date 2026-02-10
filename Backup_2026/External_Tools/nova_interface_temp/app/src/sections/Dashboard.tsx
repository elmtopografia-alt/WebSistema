import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import {
  BarChart3,
  FileText,
  Users,
  DollarSign,
  Calendar,
  CheckCircle,
  Clock,
  TrendingUp,
} from 'lucide-react';

const stats = [
  { label: 'Propostas Enviadas', value: '1.234', change: '+12%', icon: FileText },
  { label: 'Taxa de Conversão', value: '68%', change: '+5%', icon: TrendingUp },
  { label: 'Clientes Ativos', value: '456', change: '+23%', icon: Users },
  { label: 'Faturamento', value: 'R$ 2.5M', change: '+18%', icon: DollarSign },
];

const recentProposals = [
  { client: 'Construtora ABC', value: 'R$ 150.000,00', status: 'Aprovada', date: 'Hoje' },
  { client: 'Engenharia XYZ', value: 'R$ 89.500,00', status: 'Pendente', date: 'Ontem' },
  { client: 'Topografia Silva', value: 'R$ 245.000,00', status: 'Em Análise', date: '2 dias' },
  { client: 'Obras LTDA', value: 'R$ 67.800,00', status: 'Aprovada', date: '3 dias' },
];

export default function Dashboard() {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-100px' });

  return (
    <section id="dashboard" className="relative py-24 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-b from-transparent via-orange-500/5 to-transparent" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Section header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5 }}
          className="text-center mb-16"
        >
          <span className="inline-block px-4 py-2 rounded-full bg-blue-500/10 text-blue-400 text-sm font-medium mb-4 border border-blue-500/20">
            Dashboard
          </span>
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
            Visualize seu negócio em{' '}
            <span className="text-gradient-blue">tempo real</span>
          </h2>
          <p className="text-slate-400 text-lg max-w-2xl mx-auto">
            Dashboards intuitivos e completos que mostram exatamente onde está o dinheiro
            e como otimizar seus resultados.
          </p>
        </motion.div>

        {/* Dashboard preview */}
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 40 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="relative"
        >
          {/* Glow */}
          <div className="absolute -inset-4 bg-gradient-to-r from-blue-500/10 to-orange-500/10 rounded-3xl blur-2xl" />

          {/* Dashboard container */}
          <div className="relative bg-[#111827] rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
            {/* Header */}
            <div className="flex items-center justify-between px-6 py-4 bg-[#0f172a] border-b border-white/5">
              <div className="flex items-center gap-4">
                <div className="flex gap-1.5">
                  <div className="w-3 h-3 rounded-full bg-red-500" />
                  <div className="w-3 h-3 rounded-full bg-yellow-500" />
                  <div className="w-3 h-3 rounded-full bg-green-500" />
                </div>
                <span className="text-sm text-slate-500">sgt-propostas.app/dashboard</span>
              </div>
              <div className="flex items-center gap-4">
                <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5">
                  <Calendar className="w-4 h-4 text-slate-400" />
                  <span className="text-sm text-slate-300">Últimos 30 dias</span>
                </div>
                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-orange-600" />
              </div>
            </div>

            {/* Content */}
            <div className="p-6">
              {/* Stats grid */}
              <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {stats.map((stat, index) => (
                  <motion.div
                    key={stat.label}
                    initial={{ opacity: 0, y: 20 }}
                    animate={isInView ? { opacity: 1, y: 0 } : {}}
                    transition={{ duration: 0.5, delay: index * 0.1 }}
                    className="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors"
                  >
                    <div className="flex items-center justify-between mb-2">
                      <stat.icon className="w-5 h-5 text-slate-400" />
                      <span className="text-xs text-green-400 flex items-center gap-1">
                        <TrendingUp className="w-3 h-3" />
                        {stat.change}
                      </span>
                    </div>
                    <p className="text-2xl font-bold text-white mb-1">{stat.value}</p>
                    <p className="text-xs text-slate-500">{stat.label}</p>
                  </motion.div>
                ))}
              </div>

              {/* Main content grid */}
              <div className="grid lg:grid-cols-3 gap-6">
                {/* Chart */}
                <div className="lg:col-span-2 p-5 rounded-xl bg-white/5 border border-white/10">
                  <div className="flex items-center justify-between mb-4">
                    <h4 className="text-sm font-medium text-white">Desempenho Mensal</h4>
                    <div className="flex items-center gap-4">
                      <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-orange-500" />
                        <span className="text-xs text-slate-400">Propostas</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-blue-500" />
                        <span className="text-xs text-slate-400">Aprovadas</span>
                      </div>
                    </div>
                  </div>

                  {/* Bar chart */}
                  <div className="h-48 flex items-end justify-between gap-2">
                    {['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'].map((month, i) => {
                      const height1 = [60, 75, 55, 85, 70, 90][i];
                      const height2 = [40, 55, 40, 65, 50, 75][i];
                      return (
                        <div key={month} className="flex-1 flex flex-col items-center gap-2">
                          <div className="w-full flex items-end justify-center gap-1 h-36">
                            <motion.div
                              initial={{ height: 0 }}
                              animate={isInView ? { height: `${height1}%` } : {}}
                              transition={{ duration: 0.5, delay: i * 0.1 }}
                              className="w-full max-w-8 bg-gradient-to-t from-orange-600 to-orange-400 rounded-t"
                            />
                            <motion.div
                              initial={{ height: 0 }}
                              animate={isInView ? { height: `${height2}%` } : {}}
                              transition={{ duration: 0.5, delay: i * 0.1 + 0.05 }}
                              className="w-full max-w-8 bg-gradient-to-t from-blue-600 to-blue-400 rounded-t"
                            />
                          </div>
                          <span className="text-xs text-slate-500">{month}</span>
                        </div>
                      );
                    })}
                  </div>
                </div>

                {/* Recent proposals */}
                <div className="p-5 rounded-xl bg-white/5 border border-white/10">
                  <h4 className="text-sm font-medium text-white mb-4">Propostas Recentes</h4>
                  <div className="space-y-3">
                    {recentProposals.map((proposal, i) => (
                      <motion.div
                        key={proposal.client}
                        initial={{ opacity: 0, x: 20 }}
                        animate={isInView ? { opacity: 1, x: 0 } : {}}
                        transition={{ duration: 0.3, delay: i * 0.1 }}
                        className="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors"
                      >
                        <div>
                          <p className="text-sm font-medium text-white">{proposal.client}</p>
                          <p className="text-xs text-slate-500">{proposal.date}</p>
                        </div>
                        <div className="text-right">
                          <p className="text-sm font-medium text-orange-400">
                            {proposal.value}
                          </p>
                          <span
                            className={`text-xs ${
                              proposal.status === 'Aprovada'
                                ? 'text-green-400'
                                : proposal.status === 'Pendente'
                                ? 'text-yellow-400'
                                : 'text-blue-400'
                            }`}
                          >
                            {proposal.status}
                          </span>
                        </div>
                      </motion.div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Bottom stats */}
              <div className="grid grid-cols-3 gap-4 mt-6">
                {[
                  { icon: CheckCircle, label: 'Propostas Aprovadas', value: '842' },
                  { icon: Clock, label: 'Tempo Médio', value: '3.2 dias' },
                  { icon: BarChart3, label: 'Ticket Médio', value: 'R$ 45.8k' },
                ].map((item) => (
                  <div
                    key={item.label}
                    className="flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10"
                  >
                    <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center">
                      <item.icon className="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <p className="text-lg font-bold text-white">{item.value}</p>
                      <p className="text-xs text-slate-500">{item.label}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  );
}
