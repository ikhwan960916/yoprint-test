class AsyncQueue {
    constructor() {
      this.tasks = [];
      this.isProcessing = false;
    }
  
    // Enqueue a new task
    enqueue(task) {
      if (typeof task !== "function") {
        throw new Error('Task must be a function that returns a promise');
      }
      this.tasks.push(task);
      this.process();
    }
  
    // Process tasks in the queue
    async process() {
      if (this.isProcessing) return;  // If another task is currently processing, do nothing
  
      this.isProcessing = true;
  
      while (this.tasks.length > 0) {
        const currentTask = this.tasks.shift();
        try {
          await currentTask();  // Execute the task and wait for it
        } catch (error) {
          console.error('Task failed:', error);
        }
      }
  
      this.isProcessing = false;
    }
  }
  