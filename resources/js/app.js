import Alpine from 'alpinejs';
import chatApp from './chat';
import './echo';

window.Alpine = Alpine;
Alpine.data('chatApp', chatApp);

Alpine.start();
