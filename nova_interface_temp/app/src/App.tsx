import { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import Navbar from './sections/Navbar';
import Hero from './sections/Hero';
import Features from './sections/Features';
import Dashboard from './sections/Dashboard';
import Pricing from './sections/Pricing';
import Footer from './sections/Footer';

function App() {
  const [scrollY, setScrollY] = useState(0);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    setIsLoaded(true);
    const handleScroll = () => setScrollY(window.scrollY);
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <AnimatePresence>
      {isLoaded && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5 }}
          className="min-h-screen bg-[#0a0f1a] overflow-x-hidden"
        >
          {/* Background effects */}
          <div className="fixed inset-0 pointer-events-none">
            <div className="absolute inset-0 grid-bg opacity-50" />
            <div className="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl" />
            <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl" />
          </div>

          {/* Navigation */}
          <Navbar scrollY={scrollY} />

          {/* Main content */}
          <main className="relative z-10">
            <Hero />
            <Features />
            <Dashboard />
            <Pricing />
          </main>

          {/* Footer */}
          <Footer />
        </motion.div>
      )}
    </AnimatePresence>
  );
}

export default App;
