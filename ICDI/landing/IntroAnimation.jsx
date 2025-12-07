import React, { useState, useEffect } from 'react';

/**
 * BlurText Component
 * Animates text with a blur effect
 */
const BlurText = ({ 
  text, 
  className = '', 
  delay = 50, 
  animateBy = 'letters',
  onAnimationComplete 
}) => {
  const [revealedCount, setRevealedCount] = useState(0);
  const items = animateBy === 'letters' ? text.split('') : text.split(' ');

  useEffect(() => {
    if (revealedCount < items.length) {
      const timer = setTimeout(() => {
        setRevealedCount(prev => prev + 1);
      }, delay);
      return () => clearTimeout(timer);
    } else if (onAnimationComplete) {
      onAnimationComplete();
    }
  }, [revealedCount, items.length, delay, onAnimationComplete]);

  return (
    <div className={className} style={{ display: 'inline-block' }}>
      {items.map((item, index) => (
        <span
          key={index}
          style={{
            filter: index < revealedCount ? 'blur(0px)' : 'blur(10px)',
            opacity: index < revealedCount ? 1 : 0,
            transition: 'filter 0.3s ease, opacity 0.3s ease',
            display: animateBy === 'letters' ? 'inline' : 'inline-block',
            marginRight: animateBy === 'words' ? '0.5rem' : '0'
          }}
        >
          {item === ' ' ? '\u00A0' : item}
        </span>
      ))}
    </div>
  );
};

/**
 * IntroAnimation Component
 * Shows BlurText animation before revealing the main content
 */
const IntroAnimation = ({ children, introText = "ICDISG PROLWAY" }) => {
  const [showIntro, setShowIntro] = useState(true);
  const [fadeOut, setFadeOut] = useState(false);

  const handleAnimationComplete = () => {
    setTimeout(() => {
      setFadeOut(true);
      setTimeout(() => {
        setShowIntro(false);
      }, 1000);
    }, 1000);
  };

  return (
    <>
      {/* Intro Screen */}
      {showIntro && (
        <div 
          style={{
            position: 'fixed',
            inset: 0,
            zIndex: 9999,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: '#0f1112',
            opacity: fadeOut ? 0 : 1,
            transition: 'opacity 1s ease'
          }}
        >
          <BlurText 
            text={introText}
            className="intro-text"
            delay={80}
            animateBy="letters"
            onAnimationComplete={handleAnimationComplete}
          />
        </div>
      )}

      {/* Main Content */}
      {!showIntro && children}
    </>
  );
};

export default IntroAnimation;
