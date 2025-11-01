const animationSpeeds = {
  tada: 1000,
  flash: 480,
  shake: 400,
  pulseUp: 340,
  pulseUpSmall: 340,
  pulseDown: 340,
  pulseDownSmall: 340,
  popIn: 280,
  popOut: 480,
  fadeIn: 280,
  fadeOut: 280,
};

export function animate(el, animation, complete) {
  const className = `animated-${animation}`;
  const duration = animationSpeeds[animation] || 500;

  // Remove existing animation
  if (el.dataset.animating) {
    el.classList.remove(el.dataset.animating);
    clearTimeout(el.dataset.animatingTimeout);
  }

  // Apply new animation
  el.classList.add(className);
  el.dataset.animating = className;

  const timeout = setTimeout(() => {
    el.classList.remove(className);
    delete el.dataset.animating;
    delete el.dataset.animatingTimeout;
    if (complete) complete();
  }, duration);

  el.dataset.animatingTimeout = timeout;
}
