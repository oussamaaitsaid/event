import { createI18n } from 'vue-i18n';
import en from './en';
import fr from './fr';

const savedLocale = localStorage.getItem('locale') || 'en';

export default createI18n({
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: { en, fr },
});
