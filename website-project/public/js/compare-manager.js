import { showNotification } from './utils.js';

export const getCompareList = () => {
    return JSON.parse(localStorage.getItem('compareList')) || [];
};

export const addToCompare = (id) => {
    const list = getCompareList();
    // Ensure we are comparing IDs as strings or numbers consistently
    if (list.some(itemId => itemId == id)) {
        return true; // Already in list
    }
    if (list.length >= 3) {
        showNotification('You can compare up to 3 properties only.', 'warning');
        return false;
    }
    list.push(id);
    localStorage.setItem('compareList', JSON.stringify(list));
    return true;
};

export const removeFromCompare = (id) => {
    let list = getCompareList();
    list = list.filter(itemId => itemId != id);
    localStorage.setItem('compareList', JSON.stringify(list));
};

export const isInCompare = (id) => {
    const list = getCompareList();
    return list.some(itemId => itemId == id);
};