# RAG IMPLEMENTATION STRATEGY

## Overview
Retrieval-Augmented Generation (RAG) is utilized to generate highly accurate, medically-safe meal recommendations without relying on third-party APIs like Gemini. The system will consider the user's extracted medical metrics, risk scores, allergies, and favorite foods.

## Components
1. **Knowledge Base**: Curated medical nutrition guidelines (WHO, NIH, ADA) stored locally as text.
2. **Embedding Model**: `BAAI/bge-large-en-v1.5` (or a similar lightweight local embedding model).
3. **Vector Database**: ChromaDB (integrated within the Python FastAPI service).
4. **Generator**: A small local LLM (e.g., Llama-3-8B-Instruct quantized, or phi-3) running locally via `Ollama` or `vLLM` to formulate the final recommendation.

## Data Flow
1. **Context Construction**: 
   When a user requests a meal plan, the system compiles a query: 
   `"User has HbA1c of 7.2 (High Diabetes Risk), allergic to dairy, likes chicken."`
2. **Retrieval**: 
   The query is embedded and searched against ChromaDB. The system retrieves chunks like: `"For diabetic patients with HbA1c > 7.0, recommend low-glycemic index foods. Avoid lactose if dairy allergy is present."`
3. **Augmented Generation**: 
   The local LLM receives the prompt: 
   *System*: "You are a clinical nutritionist."
   *Context*: [Retrieved Guidelines]
   *User*: [Context Construction]
   The LLM generates a structured JSON output with the meal plan.

## Optimization for Accuracy
- **Strict Prompting**: The local LLM will be prompted to ONLY output JSON adhering to the context, minimizing hallucinations.
- **Filtering**: Before passing to the LLM, the system will explicitly filter out any recipes containing ingredients found in the user's `allergies` array.
- **Boosting**: Recipes containing ingredients in the user's `favorites` array will receive a vector similarity boost during retrieval.
