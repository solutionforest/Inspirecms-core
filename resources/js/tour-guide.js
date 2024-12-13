import { Boarding } from "boarding.js";

document.addEventListener('alpine:init', () => {

  window.addEventListener('tour-guide-reset', () => {
    window.Alpine.store('tourGuide').reset();
  });

  window.Alpine.store('tourGuide', {
    boarding: null,
    currentStep: window.Alpine.$persist(0).as('tourGuide-currentStep'),
    currentSubStep: 0,
    nextStep() {
      this.currentStep++;
    },
    prevStep() {
      this.currentStep--;
    },
    jumpToStep(step) {
      this.currentStep = parseInt(step);
    },
    nextSubStep() {
      this.currentSubStep++;
    },
    prevSubStep() {
      this.currentSubStep--;
    },
    jumpToSubStep(step) {
      this.currentSubStep = parseInt(step);
    },
    getCurrentStep() {
      return parseInt(this.currentStep);
    },
    getCurrentSubStep() {
      return parseInt(this.currentSubStep);
    },
    reset() {
      this.jumpToStep(0);
      this.jumpToSubStep(0);
      this.boarding = this.initBoarding();
    },
    skip() {
      this.jumpToStep(-1);
    },
    init() {
      this.ensureStepBeforeInitBoarding();
      this.boarding = this.initBoarding();
    },
    initBoarding() {
      const boarding = new Boarding({
        className: "boarding-popover-ctn", // className to wrap boarding.js popover
        animate: false, // Whether to animate or not
        opacity: 0.75, // Overlay opacity (0 means only popovers and without overlay)
        padding: 2, // Distance of element from around the edges
        allowClose: false, // Whether the click on overlay should close or not
        overlayClickNext: true, // Whether the click on overlay should move next
        overlayColor: "rgb(0,0,0)", // Fill color for the overlay
        // doneBtnText: "Done", // Text on the final button
        closeBtnText: "Skip the tour guide", // Text on the close button for this step
        // nextBtnText: "Next", // Next button text for this step
        // prevBtnText: "Previous", // Previous button text for this step
        showButtons: true, // Do not show control buttons in footer
        keyboardControl: true, // Allow controlling through keyboard (escape to close, arrow keys to move)
        scrollIntoViewOptions: {
          behaviour: "smooth",
        }, // We use `scrollIntoView()` when possible, pass here the options for it if you want any. Alternatively, you can also disable this functionallity by setting scrollIntoViewOptions to "no-scroll"
        onBeforeHighlighted: (HighlightElement) => {}, // Called when element is about to be highlighted
        onHighlighted: (HighlightElement) => {}, // Called when element is fully highlighted
        onDeselected: (HighlightElement) => { // Called when element has been deselected
        },
        onReset: (HighlightElement, reason) => { // Called when overlay is about to be cleared
          if (reason === 'cancel') {
            this.skip();
          } else {
            this.nextStep();
          }
        }, 
        onStart: (HighlightElement) => {}, // Called when `boarding.start()` was called
        onNext: (HighlightElement) => { // Called when m+oving to next step on any step
          this.nextSubStep();
          let maxStep = this.allBoardingSteps().length - 1;
          // If the current step is the last step before finish, auto move to the last step
          if (this.getCurrentStep() === maxStep - 1) {
            // Refresh the page to ensure the last step is displayed correctly
            window.location.reload();
          }
        },
        onPrevious: (HighlightElement) => { // Called when moving to previous step on any step
          this.prevSubStep();
        },
        strictClickHandling: true, // Can also be `"block-all"` or if not wanted at all, `false`. Either block ALL pointer events, or isolate pointer-events to only allow on the highlighted element (`true`). Popover and overlay pointer-events are of course always allowed to be clicked
        // Make changes to the actual popoverElements once they get rendered.
        onPopoverRender: (el) => {
          //
        },
      });
      
      if (this.isTourGuideActive()) {
        let steps  = this.getBoardingSteps(this.getCurrentStep());
        if (steps.length > 0) {
          boarding.defineSteps(steps);
          boarding.start(this.getCurrentSubStep());
        }
      }

      return boarding;
    },
    isTourGuideActive() {
      return this.getCurrentStep() > -1;
    },
    ensureStepBeforeInitBoarding() {
      if (! this.isTourGuideActive()) {
        return;
      }
      
      let step = this.getCurrentStep();
      let maxStep = this.allBoardingSteps().length - 1;

      // Ensure currentStep base on the URL path
      const path = window.location.pathname;

      if (step > maxStep) {
        // Finish the tour guide
        this.jumpToStep(-1);
        return;
      }

      if (step < 1) {
        if (path.endsWith("/settings/document-types")) {
          this.jumpToStep(1);
        }
      } 
      if (step < 2) {
        if (path.match(/\/settings\/document-types\/\d+/)?.length > 0) {
          this.jumpToStep(3);
        }
      }
      if (step < 4) {
        if (path.match(/\/content\/pages/)?.length > 0) {
          this.jumpToStep(5);
        }
      }
    },
    getBoardingSteps(step) {
      if (! this.isTourGuideActive()) {
        return [];
      }

      let allSteps = this.allBoardingSteps();

      if (step > -1 && step < allSteps.length) {
        return allSteps[step];
      }

      return [];
    },
    allBoardingSteps() {

      const generalPopoverOptions = {
        alignment: "start",
        preferredSide: "top",
      };

      return [
        // Go to "Settings" page
        [
          {
            element: ".fi-topbar-item[data-nav-key=settings]",
            popover: {
              ...generalPopoverOptions,
              title: "Go to Settings",
            },
          }
        ],
        // Go to "Document Types" page and create a new Document Type
        [
          {
            element: "[data-nav-key=settings][data-nav-item-key='document_type']",
            popover: {
              ...generalPopoverOptions,
              title: "Go to Document Types",
              prefferedSide: "right",
            },
          }, {
            element: "[data-action-name='create']",
            popover: {
              ...generalPopoverOptions,
              title: "Create a new Document Type",
              prefferedSide: "left",
              onPopoverRender: (el) => {  // Make changes to the actual popoverElements once they get rendered.
                setTimeout(() => {
                  document.querySelector("[data-action-name='create']")?.click();
                }, 30);
              },
            },
          }, {
            element: ".fi-modal-content",
            popover: {
              ...generalPopoverOptions,
              title: "Fill in the form",
            },
          }, {
            element: ".fi-modal-window button[type='submit']",
            popover: {
              ...generalPopoverOptions,
              title: "Submit the form to create a new Document Type",
              prefferedSide: "bottom",
            },
          },
        ],
        // After a Document Type is created, go to the detail page normally.
        [
          {
            element: "table > tbody > tr:first-child",
            popover: {
              ...generalPopoverOptions,
              title: "Go to edit page",
            },
          }
        ],
        // Then go to edit page to create "FieldGroup" and "Template" for the Document Type
        [
          {
            element: "[data-relation-manager-key='field_group']",
            popover: {
              ...generalPopoverOptions,
              title: "Create Fields",
              description: "A custom fields that can be used in a form while creating a content page.",
            },
          }, {
            element: "[data-relation-manager-key='templates']",
            popover: {
              ...generalPopoverOptions,
              title: "Create Template",
              description: "A template is a layout that can be used to render a content page.",
            },
          }, 
          {
            element: ".fi-topbar-item[data-nav-key=content]",
            popover: {
              ...generalPopoverOptions,
              title: "Go to Content after creating Field Group and Template",
            },
          }
        ],
        // Then go to "Content" page to create a new content page
        [
          {
            element: "button[data-action-name='create_content']",
            popover: {
              ...generalPopoverOptions,
              title: "Now you can select a Document Type to create a new content page",
            },
          }, 
        ], 
        // Finish the tour guide
        [
          {
            element: ".fi-user-menu",
            popover: {
              ...generalPopoverOptions,
              title: "Tour Guide Finished",
              description: "If you want to start the tour guide again, you can click here.",
              prefferedSide: "left",
              onPopoverRender: (el) => {  // Make changes to the actual popoverElements once they get rendered.
                setTimeout(() => {
                  document.querySelector('.fi-user-menu > .fi-dropdown-trigger')?.click();
                }, 0);
              },
            },
          }, {
            element: "button.tour-guide-reset-btn",
            popover: {
              ...generalPopoverOptions,
              title: "Reset the tour guide",
              description: "You can click this button to reset the tour guide.",
            },
          }, {
            element: ".fi-user-menu",
            popover: {
              ...generalPopoverOptions,
              className: "popover-center",
              title: "Congratulations! You have completed the tour guide."
            }
          }
        ]
      ];
    }
  });

});

document.querySelector('.tour-guide-reset-btn').addEventListener('click', () => {
  window.dispatchEvent(new CustomEvent('tour-guide-reset'))
});