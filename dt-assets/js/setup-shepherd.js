/**
 * Setup the feature tour using Shepherd
 * https://shepherdjs.dev/docs
 */

const tour = new Shepherd.Tour({
    defaultStepOptions: {
        classes: 'shadow-md bg-purple-dark',
        //        scrollTo: true
    },
    useModalOverlay: true,
});

function createStep(selector, position, shepherdOptions = {}, options = {}) {
    const defaultOptions = {
        firstStep: false,
    }

    const opts = { ...defaultOptions, ...options }

    tour.addStep({
        attachTo: {
            element: selector,
            on: position,
        },
        cancelIcon: {
            enabled: true,
        },
        beforeShowPromise: function () {
            return new Promise(function (resolve) {
                const element = document.querySelector(selector)
                if (!element) {
                    return
                }
                resolve()
            })
        },
        ...shepherdOptions,
        buttons: [
            {
                text: opts.firstStep ? 'Done' : 'Back',
                action: opts.firstStep ? tour.cancel : tour.back
            },
            {
                text: 'Next',
                action: tour.next,
            }
        ],
    })
}

createStep('.create-post-desktop', 'bottom', {
    id: 'create-contact',
    text: 'Click here to create a new contact.',
}, {
    firstStep: true,
});

createStep('.filter-posts-desktop', 'bottom', {
    id: 'filter-contacts',
    text: 'You can filter to find contacts you need.',
});

createStep('#records-table', 'top-start', {
    id: 'view-contacts',
    text: 'Contacts appear here and can be clicked on to view more.',
});

tour.start()