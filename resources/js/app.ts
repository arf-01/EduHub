import './bootstrap';
import '../css/app.css';

import { mount } from 'svelte';
import App from './App.svelte';

const appElement = document.getElementById('app');

if (appElement) {
    mount(App, {
        target: appElement,
    });
}
